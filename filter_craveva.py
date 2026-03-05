import re
import sys

INPUT_FILE = 'craveva_test.sql'
OUTPUT_FILE = 'craveva_ai.sql'
TARGET_COMPANY_ID = 20

def split_rows(values_str):
    rows = []
    current_row_start = 0
    in_quote = False
    escape = False
    depth = 0
    
    for i, char in enumerate(values_str):
        if in_quote:
            if escape:
                escape = False
            elif char == '\\':
                escape = True
            elif char == "'":
                in_quote = False
        else:
            if char == "'":
                in_quote = True
            elif char == '(':
                if depth == 0:
                    current_row_start = i
                depth += 1
            elif char == ')':
                depth -= 1
                if depth == 0:
                    rows.append(values_str[current_row_start:i+1])
                    
    return rows

def parse_row_values(row_str):
    inner = row_str[1:-1]
    values = []
    current_val = []
    in_quote = False
    escape = False
    
    for char in inner:
        if in_quote:
            if escape:
                escape = False
            elif char == '\\':
                escape = True
            elif char == "'":
                in_quote = False
            current_val.append(char)
        else:
            if char == "'":
                in_quote = True
                current_val.append(char)
            elif char == ',':
                values.append("".join(current_val).strip())
                current_val = []
            else:
                current_val.append(char)
    
    values.append("".join(current_val).strip())
    return values

def clean_val(v):
    if v == 'NULL': return None
    if v.startswith("'") and v.endswith("'"): return v[1:-1]
    return v

def main():
    print("Reading input file...")
    with open(INPUT_FILE, 'r', encoding='utf-8') as f:
        lines = f.readlines()
        
    print(f"Read {len(lines)} lines.")

    tables = {}
    
    # Pass 1: Build Schema
    print("Building schema...")
    i = 0
    while i < len(lines):
        line = lines[i].strip()
        if line.startswith("CREATE TABLE"):
            m = re.search(r'CREATE TABLE IF NOT EXISTS `(\w+)`', line)
            if m:
                table_name = m.group(1)
                body_lines = []
                j = i + 1
                while j < len(lines):
                    l = lines[j].strip()
                    if l.startswith(") ENGINE=") or l == ");":
                        break
                    body_lines.append(l)
                    j += 1
                
                columns = []
                fks = []
                company_id_col = None
                
                for l in body_lines:
                    cm = re.match(r'`(\w+)`', l)
                    if cm:
                        col = cm.group(1)
                        columns.append(col)
                        if col == 'company_id':
                            company_id_col = col
                    
                    fm = re.search(r'FOREIGN KEY \(`(\w+)`\) REFERENCES `(\w+)`', l)
                    if fm:
                        fks.append({'col': fm.group(1), 'ref_table': fm.group(2)})
                        
                tables[table_name] = {
                    'columns': columns,
                    'fks': fks,
                    'company_id_col': company_id_col
                }
                i = j
        i += 1
        
    print(f"Found {len(tables)} tables.")

    # Determine Tenant/Global status
    is_tenant = {}
    for t in tables:
        if t == 'companies':
            is_tenant[t] = True
        elif tables[t]['company_id_col']:
            is_tenant[t] = True
        else:
            is_tenant[t] = False
            
    changed = True
    while changed:
        changed = False
        for t in tables:
            if not is_tenant[t]:
                for fk in tables[t]['fks']:
                    if is_tenant.get(fk['ref_table']):
                        is_tenant[t] = True
                        changed = True
                        break
    
    print("Parsing data...")
    all_data = {} 
    
    i = 0
    while i < len(lines):
        line = lines[i].strip()
        if line.startswith("INSERT INTO"):
            m = re.match(r'INSERT INTO `(\w+)` \((.*?)\) VALUES', line)
            if not m:
                 m = re.match(r'INSERT INTO `(\w+)` VALUES', line)
                 
            if m:
                table = m.group(1)
                cols_str = m.group(2) if len(m.groups()) > 1 else ""
                
                if cols_str:
                    cols = [c.strip().strip('`') for c in cols_str.split(',')]
                else:
                    cols = tables[table]['columns']
                    
                values_block = []
                # Check if VALUES is at the end of the line
                if line.endswith("VALUES"):
                    j = i + 1
                else:
                    # If VALUES is not at the end, it might be inline or partially inline
                    # But regex matched up to VALUES.
                    # We need to extract the part AFTER VALUES on the same line
                    post_values = line[m.end():].strip()
                    if post_values:
                        values_block.append(post_values)
                    j = i + 1
                    
                while j < len(lines):
                    l = lines[j].strip()
                    values_block.append(l)
                    if l.endswith(";"):
                        break
                    j += 1
                    
                full_values_str = " ".join(values_block)
                full_values_str = full_values_str.rstrip(';')
                
                rows = split_rows(full_values_str)
                
                if table not in all_data:
                    all_data[table] = []
                    
                for r_raw in rows:
                    vals = parse_row_values(r_raw)
                    r_map = {}
                    for k, c in enumerate(cols):
                        if k < len(vals):
                            r_map[c] = vals[k]
                    
                    all_data[table].append({'raw': r_raw, 'map': r_map})
                    
                i = j
        i += 1
        
    print("Filtering data...")
    kept_ids = {t: set() for t in tables}
    
    # 1. Companies
    if 'companies' in all_data:
        for row in all_data['companies']:
            rid = clean_val(str(row['map'].get('id')))
            if rid == str(TARGET_COMPANY_ID):
                kept_ids['companies'].add(rid)
                
    # 2. Tenant Tables with company_id
    for t in tables:
        if t != 'companies' and tables[t]['company_id_col']:
            col = tables[t]['company_id_col']
            for row in all_data.get(t, []):
                cid = clean_val(str(row['map'].get(col)))
                if cid is None or cid == str(TARGET_COMPANY_ID):
                    rid = clean_val(str(row['map'].get('id')))
                    if rid: kept_ids[t].add(rid)

    # 3. Dependent Tables (Iterative)
    changed = True
    iteration = 0
    while changed:
        changed = False
        iteration += 1
        print(f"Propagation iteration {iteration}...")
        for t in tables:
            if is_tenant[t] and t != 'companies' and not tables[t]['company_id_col']:
                current_count = len(kept_ids[t])
                
                for row in all_data.get(t, []):
                    rid = clean_val(str(row['map'].get('id')))
                    if rid and rid in kept_ids[t]:
                        continue
                        
                    should_keep = False
                    for fk in tables[t]['fks']:
                        ref = fk['ref_table']
                        col = fk['col']
                        if is_tenant.get(ref):
                            val = clean_val(str(row['map'].get(col)))
                            if val in kept_ids[ref]:
                                should_keep = True
                                break
                    
                    if should_keep:
                        if rid: kept_ids[t].add(rid)
                        
                if len(kept_ids[t]) > current_count:
                    changed = True

    print("Writing output...")
    written_tables = set()
    
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        f.write("-- Filtered for Company 20\n\n")
        
        i = 0
        while i < len(lines):
            line = lines[i]
            
            if line.strip().startswith("INSERT INTO"):
                m = re.match(r'INSERT INTO `(\w+)`', line.strip())
                if m:
                    table = m.group(1)
                    
                    # Skip to end of block
                    j = i
                    if line.strip().endswith("VALUES"):
                        j += 1
                    else:
                        # Logic to handle inline values
                         pass
                         
                    while j < len(lines):
                        if lines[j].strip().endswith(";"):
                            break
                        j += 1
                    
                    if table not in written_tables:
                        written_tables.add(table)
                        
                        final_rows = []
                        if not is_tenant[table]:
                            final_rows = [r['raw'] for r in all_data.get(table, [])]
                        else:
                            for row in all_data.get(table, []):
                                keep = False
                                if table == 'companies':
                                     rid = clean_val(str(row['map'].get('id')))
                                     if rid == str(TARGET_COMPANY_ID): keep = True
                                elif tables[table]['company_id_col']:
                                     col = tables[table]['company_id_col']
                                     cid = clean_val(str(row['map'].get(col)))
                                     if cid is None or cid == str(TARGET_COMPANY_ID): keep = True
                                else:
                                     for fk in tables[table]['fks']:
                                        ref = fk['ref_table']
                                        col = fk['col']
                                        if is_tenant.get(ref):
                                            val = clean_val(str(row['map'].get(col)))
                                            if val in kept_ids[ref]:
                                                keep = True
                                                break
                                
                                if keep:
                                    final_rows.append(row['raw'])

                        if final_rows:
                            cols = tables[table]['columns']
                            header = "INSERT INTO `{}` ({}) VALUES".format(
                                table, ", ".join(["`"+c+"`" for c in cols])
                            )
                            f.write(header + "\n")
                            f.write(",\n".join(final_rows) + ";\n")
                    
                    i = j
                else:
                    i += 1
            else:
                f.write(line)
            i += 1
            
    print("Done.")

if __name__ == '__main__':
    main()

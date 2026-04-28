# Angular Installation Guide

## Installation

1. Ensure you have Angular CLI installed
2. Copy the component files
3. Add to your Angular module

```typescript
import { CravevaAgentComponent } from './craveva-agent.component';

@NgModule({
  declarations: [
    CravevaAgentComponent
  ]
})
export class AppModule { }
```

Then use in templates:
```html
<craveva-agent [agentId]="'YOUR_AGENT_ID'"></craveva-agent>
```

⭐ **YOUR SELECTED FORMAT** ⭐

# Javascript Installation Guide

## Quick Install

Copy the widget code from `widget.js` and paste it into your HTML file before the closing `</body>` tag.

```html
<!-- Paste widget code here -->
<script>
  (function() {
    const cravevaScript = document.createElement('script');
    cravevaScript.src = 'YOUR_API_BASE/agents/YOUR_AGENT_ID/widget.js';
    cravevaScript.setAttribute('data-agent-id', 'YOUR_AGENT_ID');
    document.head.appendChild(cravevaScript);
  })();
</script>
```

## That's it!

The widget will automatically load and appear on your page.

# Vue Installation Guide

## Installation

1. Install Vue 3: `npm install vue@next`
2. Copy the component code from `CravevaAgent.vue`
3. Import and use in your Vue app

```vue
<template>
  <CravevaAgent :agentId="'YOUR_AGENT_ID'" />
</template>

<script>
import CravevaAgent from './CravevaAgent.vue';

export default {
  components: {
    CravevaAgent
  }
};
</script>
```

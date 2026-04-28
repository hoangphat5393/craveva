# Node.js Integration

## Installation

```bash
npm install axios
```

## Usage

```javascript
const CravevaAgent = require('./agent');

const agent = new CravevaAgent(
  'YOUR_AGENT_ID',
  'YOUR_API_KEY',
  'https://ai.craveva.com'
);

agent.chat('Hello, how can you help me?')
  .then(response => {
    console.log(response.output);
  })
  .catch(error => {
    console.error('Error:', error);
  });
```

## Features

- Promise-based API
- Automatic error handling
- Configurable timeout
- Ready for Express.js integration

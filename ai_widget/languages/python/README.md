# Python Integration

## Installation

```bash
pip install requests
```

## Usage

```python
from agent import CravevaAgent

agent = CravevaAgent(
    agent_id='YOUR_AGENT_ID',
    api_key='YOUR_API_KEY',
    base_url='https://ai.craveva.com'
)

response = agent.chat("Hello, how can you help me?")
print(response['output'])
```

## Features

- Simple chat interface
- Optional user/company/outlet ID tracking
- Error handling included
- Ready to use in your Python application

# Ruby Integration

## Installation

```bash
gem install httparty
```

## Usage

```ruby
require_relative 'agent'

agent = CravevaAgent.new(
  'YOUR_AGENT_ID',
  'YOUR_API_KEY',
  'https://ai.craveva.com'
)

response = agent.chat('Hello, how can you help me?')
puts response['output']
```

## Features

- Simple Ruby class
- HTTParty for HTTP requests
- Error handling included
- Ready for Rails integration

# Ruby Integration for Craveva AI Agent
# Install: gem install httparty

require 'httparty'
require 'json'

class CravevaAgent
  def initialize(agent_id = '69ccc35e7d0ece6ff702487b', api_key = 'cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE', base_url = 'https://ai.craveva.com')
    @agent_id = agent_id
    @api_key = api_key
    @base_url = base_url
    @endpoint = "#{base_url}/api/v1/agents/#{agent_id}/chat"
  end

  def chat(message, user_id: nil, company_id: nil, outlet_id: nil)
    headers = {
      'Content-Type' => 'application/json'
    }
    
    headers['Authorization'] = "Bearer #{@api_key}" if @api_key

    payload = {
      message: message
    }
    
    payload[:user_id] = user_id if user_id
    payload[:company_id] = company_id if company_id
    payload[:outlet_id] = outlet_id if outlet_id

    begin
      response = HTTParty.post(@endpoint, {
        headers: headers,
        body: payload.to_json,
        timeout: 30
      })

      if response.success?
        JSON.parse(response.body)
      else
        { error: "HTTP #{response.code}: #{response.message}", output: 'Sorry, I could not process that request.' }
      end
    rescue => e
      { error: e.message, output: 'Sorry, I could not process that request.' }
    end
  end

  def get_response(message)
    result = chat(message)
    result['output'] || 'Sorry, I could not process that request.'
  end
end

# Usage example:
# agent = CravevaAgent.new(
#   '69ccc35e7d0ece6ff702487b',
#   'cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE',
#   'https://ai.craveva.com'
# )
# response = agent.chat('Hello, how can you help me?')
# puts response['output']

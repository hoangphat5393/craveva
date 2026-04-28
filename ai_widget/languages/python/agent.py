# Python Integration for Craveva AI Agent
# Install: pip install requests

import requests
import json

class CravevaAgent:
    def __init__(self, agent_id='69ccc35e7d0ece6ff702487b', api_key='cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE', base_url='https://ai.craveva.com'):
        self.agent_id = agent_id
        self.api_key = api_key
        self.base_url = base_url
        self.api_endpoint = f"{base_url}/api/v1/agents/{agent_id}/chat"
    
    def chat(self, message, user_id=None, company_id=None, outlet_id=None):
        """
        Send a message to the agent and get a response.
        
        Args:
            message (str): The message to send to the agent
            user_id (str, optional): User ID for tracking
            company_id (str, optional): Company ID for data filtering
            outlet_id (str, optional): Outlet ID for data filtering
        
        Returns:
            dict: Response from the agent
        """
        headers = {
            'Content-Type': 'application/json',
        }
        
        if self.api_key:
            headers['Authorization'] = f'Bearer {self.api_key}'
        
        payload = {
            'message': message
        }
        
        if user_id:
            payload['user_id'] = user_id
        if company_id:
            payload['company_id'] = company_id
        if outlet_id:
            payload['outlet_id'] = outlet_id
        
        try:
            response = requests.post(self.api_endpoint, json=payload, headers=headers)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            print(f"Error communicating with agent: {e}")
            return {'error': str(e)}
    
    def stream_chat(self, message, callback=None):
        """
        Stream chat responses (if supported).
        
        Args:
            message (str): The message to send
            callback (callable, optional): Callback function for each chunk
        
        Returns:
            generator: Response chunks
        """
        # Implementation for streaming would go here
        response = self.chat(message)
        if callback:
            callback(response)
        return response


# Usage example:
if __name__ == '__main__':
    agent = CravevaAgent(
        agent_id='69ccc35e7d0ece6ff702487b',
        api_key='cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE',
        base_url='https://ai.craveva.com'
    )
    
    # Send a message
    response = agent.chat("Hello, how can you help me?")
    print(f"Agent response: {response.get('output', 'No response')}")

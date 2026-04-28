// C# / .NET Integration for Craveva AI Agent
// Install: dotnet add package Newtonsoft.Json

using System;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;

public class CravevaAgentService
{
    private readonly string agentId = "69ccc35e7d0ece6ff702487b";
    private readonly string apiKey = "cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE";
    private readonly string baseUrl = "https://ai.craveva.com";
    private readonly HttpClient httpClient;

    public CravevaAgentService(HttpClient httpClient)
    {
        this.httpClient = httpClient;
    }

    /// <summary>
    /// Send a message to the agent
    /// </summary>
    public async Task<AgentResponse> ChatAsync(string message, string userId = null, string companyId = null, string outletId = null)
    {
        var endpoint = $"{baseUrl}/api/v1/agents/{agentId}/chat";
        
        var payload = new
        {
            message = message,
            user_id = userId,
            company_id = companyId,
            outlet_id = outletId
        };

        var json = JsonConvert.SerializeObject(payload);
        var content = new StringContent(json, Encoding.UTF8, "application/json");

        var request = new HttpRequestMessage(HttpMethod.Post, endpoint)
        {
            Content = content
        };

        if (!string.IsNullOrEmpty(apiKey))
        {
            request.Headers.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", apiKey);
        }

        try
        {
            var response = await httpClient.SendAsync(request);
            response.EnsureSuccessStatusCode();
            
            var responseContent = await response.Content.ReadAsStringAsync();
            return JsonConvert.DeserializeObject<AgentResponse>(responseContent);
        }
        catch (Exception ex)
        {
            return new AgentResponse
            {
                Error = ex.Message,
                Output = "Sorry, I could not process that request."
            };
        }
    }
}

public class AgentResponse
{
    [JsonProperty("output")]
    public string Output { get; set; }
    
    [JsonProperty("error")]
    public string Error { get; set; }
}

// Usage example:
// var httpClient = new HttpClient();
// var agent = new CravevaAgentService(httpClient);
// var response = await agent.ChatAsync("Hello, how can you help me?");
// Console.WriteLine(response.Output);

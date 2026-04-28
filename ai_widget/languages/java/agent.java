// Java/Spring Boot Integration for Craveva AI Agent
// Add to your Spring Boot service

package com.yourcompany.service;

import org.springframework.http.*;
import org.springframework.stereotype.Service;
import org.springframework.web.client.RestTemplate;
import org.springframework.web.client.HttpClientErrorException;
import java.util.HashMap;
import java.util.Map;

@Service
public class CravevaAgentService {
    
    private final String agentId = "69ccc35e7d0ece6ff702487b";
    private final String apiKey = "cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE";
    private final String baseUrl = "https://ai.craveva.com";
    private final RestTemplate restTemplate;
    
    public CravevaAgentService(RestTemplate restTemplate) {
        this.restTemplate = restTemplate;
    }
    
    /**
     * Send a message to the agent
     *
     * @param message The message to send
     * @param userId Optional user ID
     * @param companyId Optional company ID
     * @param outletId Optional outlet ID
     * @return Response from the agent
     */
    public Map<String, Object> chat(String message, String userId, String companyId, String outletId) {
        String endpoint = baseUrl + "/api/v1/agents/" + agentId + "/chat";
        
        HttpHeaders headers = new HttpHeaders();
        headers.setContentType(MediaType.APPLICATION_JSON);
        
        if (apiKey != null && !apiKey.isEmpty()) {
            headers.setBearerAuth(apiKey);
        }
        
        Map<String, Object> payload = new HashMap<>();
        payload.put("message", message);
        
        if (userId != null) payload.put("user_id", userId);
        if (companyId != null) payload.put("company_id", companyId);
        if (outletId != null) payload.put("outlet_id", outletId);
        
        HttpEntity<Map<String, Object>> request = new HttpEntity<>(payload, headers);
        
        try {
            ResponseEntity<Map> response = restTemplate.postForEntity(
                endpoint,
                request,
                Map.class
            );
            
            return response.getBody();
        } catch (HttpClientErrorException e) {
            Map<String, Object> errorResponse = new HashMap<>();
            errorResponse.put("error", e.getMessage());
            errorResponse.put("output", "Sorry, I could not process that request.");
            return errorResponse;
        }
    }
    
    /**
     * Send a message and return only the output text
     *
     * @param message The message to send
     * @return The agent's response text
     */
    public String getResponse(String message) {
        Map<String, Object> response = chat(message, null, null, null);
        return (String) response.getOrDefault("output", "Sorry, I could not process that request.");
    }
}

// Usage in a Controller:
// 
// @RestController
// @RequestMapping("/api/chat")
// public class ChatController {
//     
//     @Autowired
//     private CravevaAgentService agentService;
//     
//     @PostMapping
//     public ResponseEntity<Map<String, Object>> chat(@RequestBody Map<String, String> request) {
//         String message = request.get("message");
//         Map<String, Object> response = agentService.chat(message, null, null, null);
//         return ResponseEntity.ok(response);
//     }
// }

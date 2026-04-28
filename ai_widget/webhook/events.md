# Webhook Events

## Available Events

### message.received
User sends a message to your system.

**Request:**
```json
{
  "event": "message.received",
  "data": {
    "message": "User message text",
    "user_id": "optional-user-id",
    "metadata": {}
  },
  "signature": "hmac-signature"
}
```

**Response:**
```json
{
  "success": true,
  "agent_response": "Agent response text",
  "response_time_ms": 1234
}
```

### agent.response
Agent sends a response (for logging/analytics).

### deployment.status
Deployment status changes (for monitoring).

# Webhook Integration Guide

## Webhook Configuration

- **Webhook URL**: `https://ai.craveva.com/api/v1/webhooks/deployments/69ccc3cb7d0ece6ff7025911`
- **Webhook Secret**: `n2XqgTmLiQPmA7Gq9BYWBksLJdpSntuzIHHHNx1cjc8` (see `webhook-config.json`)

## Setup

1. Configure your system to send webhooks to the URL above
2. Include the webhook secret in your requests (for verification)
3. Send events as described in `events.md`

## Security

- Always verify webhook signatures using the secret
- Use HTTPS for webhook URLs
- Rotate secrets regularly

## Events

See `events.md` for available webhook events.

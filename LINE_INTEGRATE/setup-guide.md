# Line Setup Guide

## Step 1: Configure Webhook

1. Log in to your line developer console
2. Navigate to Webhook settings
3. Set webhook URL to: `https://ai.craveva.com/api/v1/webhooks/line`
4. Save the configuration

## Step 2: Verify Webhook

Use the verification code provided in `verification-code.js` to verify your webhook.

## Step 3: Test

Send a test message to verify the integration is working.

## Troubleshooting

- Ensure webhook URL is accessible from the internet
- Check that verification code matches
- Verify agent is enabled in Craveva dashboard

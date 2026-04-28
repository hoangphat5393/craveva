# C# / .NET Integration

## Installation

```bash
dotnet add package Newtonsoft.Json
```

## Usage

```csharp
var httpClient = new HttpClient();
var agent = new CravevaAgentService(httpClient);

var response = await agent.ChatAsync("Hello, how can you help me?");
Console.WriteLine(response.Output);
```

## Dependency Injection

Register in `Startup.cs`:

```csharp
services.AddHttpClient<CravevaAgentService>();
```

## Features

- Async/await support
- HttpClient integration
- JSON serialization
- Error handling included

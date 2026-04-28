# Java/Spring Boot Integration

## Installation

Add to your `pom.xml` or `build.gradle`:

```xml
<!-- Spring Boot Web Starter (if not already included) -->
<dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-web</artifactId>
</dependency>
```

## Usage

```java
@Autowired
private CravevaAgentService agentService;

public void chat() {
    Map<String, Object> response = agentService.chat(
        "Hello, how can you help me?",
        null, // userId
        null, // companyId
        null  // outletId
    );
    System.out.println(response.get("output"));
}
```

## Features

- Spring Boot service
- Dependency injection ready
- Error handling included
- Type-safe responses

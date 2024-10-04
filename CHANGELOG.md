# Changelog

All notable changes to `laravel-openid-connect` will be documented in this file.

### 0.0.1 - 2024-10-04

feat: Update OpenID Connect Integration with Enhanced Configurations and Interfaces

- Refactored Service Provider:
  
  - Updated composer.json to reflect proper namespace changes.
  - Renamed LaravelOpenidConnectServiceProvider to LaravelOpenIdConnectServiceProvider.
  
- Enhanced Configuration:
  
  - Added detailed comments in config/openid-connect.php for better documentation.
  - Introduced multiple OIDC providers configurations: Google, Okta, and Azure.
  
- New Interfaces:
  
  - Introduced AuthorizationDataContract for encapsulating authorization data responsibilities.
  - Added LaravelOpenIdConnectContract for defining the main OpenID Connect flow.
  - Created LaravelOpenIdConnectServiceContract for HTTP interaction responsibilities.
  
- Data Transfer Object:
  
  - Added AuthorizationData DTO implementing AuthorizationDataContract, providing methods for access token data, refresh token data, issuer URL, and query parameters.
  
- New Exception:
  
  - Introduced InvalidProviderConfigurationException to handle missing or invalid provider configurations.
  
- Laravel OpenID Connect Implementation:
  
  - Implemented LaravelOpenIdConnect class providing methods for generation of authorization URL, obtaining access token, user info retrieval, and token refresh functionality.
  
- Service Implementation:
  
  - Implemented LaravelOpenIdConnectService providing stateless HTTP interactions (GET and POST) with OpenID Connect providers.
  
- Testing:
  
  - Added comprehensive tests for DTOs, service class, and main OpenID Connect class covering validations, HTTP interactions, and configuration assertions.
  
- Code Updates:
  
  - Removed obsolete files and updated namespace usages across the project for consistency.
  - Added stricter type declarations and improved code comments for better clarity and maintainability.
  

These changes aim to enhance the flexibility, configurability, and reliability of the OpenID Connect integration within the Laravel package.

## 0.0.1 - 2024-10-04

**Full Changelog**: https://github.com/CreativeCrafts/laravel-openid-connect/commits/0.0.1

### 0.0.1 - 2024-10-04

feat: Update OpenID Connect Integration with Enhanced Configurations and Interfaces

- Refactored Service Provider:
  
  - Updated composer.json to reflect proper namespace changes.
  - Renamed LaravelOpenidConnectServiceProvider to LaravelOpenIdConnectServiceProvider.
  
- Enhanced Configuration:
  
  - Added detailed comments in config/openid-connect.php for better documentation.
  - Introduced multiple OIDC providers configurations: Google, Okta, and Azure.
  
- New Interfaces:
  
  - Introduced AuthorizationDataContract for encapsulating authorization data responsibilities.
  - Added LaravelOpenIdConnectContract for defining the main OpenID Connect flow.
  - Created LaravelOpenIdConnectServiceContract for HTTP interaction responsibilities.
  
- Data Transfer Object:
  
  - Added AuthorizationData DTO implementing AuthorizationDataContract, providing methods for access token data, refresh token data, issuer URL, and query parameters.
  
- New Exception:
  
  - Introduced InvalidProviderConfigurationException to handle missing or invalid provider configurations.
  
- Laravel OpenID Connect Implementation:
  
  - Implemented LaravelOpenIdConnect class providing methods for generation of authorization URL, obtaining access token, user info retrieval, and token refresh functionality.
  
- Service Implementation:
  
  - Implemented LaravelOpenIdConnectService providing stateless HTTP interactions (GET and POST) with OpenID Connect providers.
  
- Testing:
  
  - Added comprehensive tests for DTOs, service class, and main OpenID Connect class covering validations, HTTP interactions, and configuration assertions.
  
- Code Updates:
  
  - Removed obsolete files and updated namespace usages across the project for consistency.
  - Added stricter type declarations and improved code comments for better clarity and maintainability.
  

These changes aim to enhance the flexibility, configurability, and reliability of the OpenID Connect integration within the Laravel package.

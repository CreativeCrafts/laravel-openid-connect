### 0.0.3 - 2024-10-21
- Initial Release

### Added
- #7: Added 100% test coverage to OpenIDConnectManager service class.
- #5: Added 100% test coverage to OpenIDConnectJWTProcessor service class.

### Fixed
- Fixed issue with manager process response.
- Fixed static analysis error.

### Other
- Various styling fixes and improvements.

### 0.0.4 - 2025-02-25

Refactor: improve HTTP client and provider configuration handling
- Split HTTP client fetch method into separate GET and POST methods for better clarity
- Rename fetchURL to fetchViaPostMethod to better reflect its purpose
- Add support for handling not supported provider configuration keys
- Update all service classes to use the new HTTP client methods
- Add tests for the new GET method implementation

### 0.0.5 - 2025-02-25

Fixed: update JWKS endpoint to use GET method instead of POST
- The commit changes how we fetch the JSON Web Key Set (JWKS) from the OpenID Provider by using GET method instead of POST. 
 This aligns better with standard OIDC implementations where JWKS endpoints typically expect GET requests.
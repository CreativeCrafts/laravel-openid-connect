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

### 0.0.6 - 2025-03-03
- Added support for Laravel 12

### 1.0.0 - 2025-09-01

Added
- Pluggable token storage abstraction with TokenStorageContract and implementations for session, cache (with optional TTL), and stateless (none).
- Service container binding for TokenStorageContract to resolve the appropriate storage driver based on configuration.
- Configuration options to control storage (openid-connect.storage), session key prefix, cache store, and cache TTL.
- State bundle persistence keyed by state (nonce and optional code_verifier) to support robust state validation across flows.
- New tests: Base64Helper encoding/decoding, OpenIDConnectManager JWT claims verification, and TokenManager state bundle lifecycle.

Changed
- TokenManager refactored to use the pluggable storage layer; commitSession now delegates to the storage backend.
- Authorization Code and Implicit flows now load nonce/code_verifier from a state-scoped bundle and aggressively clear transient secrets (nonce, code_verifier, bundle) after use.
- PKCE handling improved: generate S256/plain challenges when the provider advertises support; always clear code_verifier after a token request attempt.
- Configuration file updated with storage-related options and defaults.

Security
- Hardened state and nonce handling; clearer exceptions around invalid/missing state, redirect URL, client ID, scope, and JWT processing.

BREAKING CHANGE
- Session key names are normalised (nonce/state/code_verifier) and managed through a pluggable storage layer
- getClientID/getClientSecret are now non-nullable
- Base64Helper::base64urlDecode signature now returns string

### 1.0.1 - 2025-09-01

Chore: clean up and improve documentation
### 1.0.3 - 2025-09-01
feat (token-manager): bind state via app-key HMAC; add replay tombstones

- Replace session-ID–based SID with HMAC of the state using app.key and enforce
  it when reading bundles to prevent cross-session state reuse.
- Write a short-lived tombstone after a state bundle is consumed to mitigate
  replay attempts.
- Accept cache_ttl as int|DateInterval|DateTimeInterface and pass through to the
  cache repo; apply sensible defaults for storage driver, cache store, and key
  prefix.
- Use config() helper instead of Config::string/integer for Laravel 10–12
  compatibility.

### 1.0.2 - 2025-09-01
Added
- Bind state bundles to the current session via a session-bound sid to prevent cross-session reuse.
- Add replay protection by writing a short-lived tombstone after a state bundle is consumed.

Changed
- The manager no longer persists legacy session keys (nonce, state, code_verifier); values are managed exclusively via state-scoped bundles, and legacy keys are cleared to avoid duplication.
- When PKCE is used, code_verifier is only stored inside the state bundle (not in legacy session storage).

Deprecated
- TokenManager legacy per-key methods (set/getNonce, set/getState, set/getCodeVerifier) are now deprecated in favour of saveStateBundle()/loadStateBundle(). They remain for backward compatibility.

Security
- Enforce session binding when loading state bundles; reject mismatched sessions.
- Reject callback processing when a consumed state is detected (tombstoned), preventing replay attacks.

Docs
- Expand README with guidance on state/session management, distributed cache considerations, and common pitfalls.

Tests
- Add end-to-end flow and TokenManager session-binding tests to cover the new behaviour.

### 1.0.1 - 2025-09-01

Chore: clean up and improve documentation

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

### 0.0.6 - 2025-03-03
- Added support for Laravel 12

### 0.0.5 - 2025-02-25

Fixed: update JWKS endpoint to use GET method instead of POST
- The commit changes how we fetch the JSON Web Key Set (JWKS) from the OpenID Provider by using the GET method instead of POST. 
 This aligns better with standard OIDC implementations where JWKS endpoints typically expect GET requests.

### 0.0.4 - 2025-02-25

Refactor: improve HTTP client and provider configuration handling
- Split the HTTP client fetch method into separate GET and POST methods for better clarity
- Rename fetchURL to fetchViaPostMethod to better reflect its purpose
- Add support for handling not supported provider configuration keys
- Update all service classes to use the new HTTP client methods
- Add tests for the new GET method implementation

### 0.0.3 - 2024-10-21
- Initial Release

### Added
- #7: Added 100% test coverage to OpenIDConnectManager service class.
- #5: Added 100% test coverage to OpenIDConnectJWTProcessor service class.

### Fixed
- Fixed the issue with the manager process response.
- Fixed the static analysis error.

### Other
- Various styling fixes and improvements.







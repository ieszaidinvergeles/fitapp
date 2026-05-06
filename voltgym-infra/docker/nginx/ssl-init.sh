#!/bin/sh
#
# SSL Certificate Management script.
#
# Responsibility: Ensures that SSL termination is always functional by detecting 
#                 existing certificates or providing secure fallback alternatives.
#
set -e

SSL_DIR="/etc/nginx/ssl"
CERT_FILE="$SSL_DIR/certificate.crt"
KEY_FILE="$SSL_DIR/private.key"

echo "Checking SSL certificates in $SSL_DIR..."

# Automatic certificate verification and fallback generation.
if [ ! -f "$CERT_FILE" ] || [ ! -f "$KEY_FILE" ]; then
    echo "WARNING: SSL certificates not found or incomplete."
    echo "Generating temporary self-signed certificates for local development/fallback..."
    
    if ! command -v openssl >/dev/null 2>&1; then
        apk add --no-cache openssl
    fi
    
    # Backup partial files if they exist so the user doesn't lose them
    [ -f "$CERT_FILE" ] && mv "$CERT_FILE" "$CERT_FILE.bak"
    [ -f "$KEY_FILE" ] && mv "$KEY_FILE" "$KEY_FILE.bak"
    
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$KEY_FILE" \
        -out "$CERT_FILE" \
        -subj "/C=ES/ST=Local/L=Local/O=VoltGym/CN=localhost"
    
    echo "SUCCESS: Self-signed certificates generated successfully."
else
    echo "SUCCESS: Real SSL certificates detected."
fi

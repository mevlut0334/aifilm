#!/bin/bash
# Bu script sadece Linux/Mac sistemlerde çalıştırılmalıdır
# Windows'ta çalıştırmanıza gerek yok

echo "Setting up file permissions..."

# Shell scriptlere execute izni ver
chmod +x deploy.sh
chmod +x redeploy.sh
chmod +x force-redeploy.sh
chmod +x setup-permissions.sh

# Storage ve cache klasörlerine izin ver
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "✅ Permissions set successfully!"
echo ""
echo "You can now run:"
echo "  ./deploy.sh         - For initial deployment"
echo "  ./redeploy.sh       - For updates from GitHub"
echo "  ./force-redeploy.sh - Force update (removes local changes)"

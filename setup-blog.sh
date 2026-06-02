#!/bin/bash

# Setup script for blog and asset management system

set -e

echo "🚀 Setting up Blog and Asset Management System..."
echo ""

# Create uploads directory
echo "📁 Creating upload directory..."
mkdir -p public/uploads/assets
chmod 755 public/uploads/assets
echo "✅ Upload directory created"
echo ""

# Display migration command
echo "📊 Next, run the database migration:"
echo "  docker-compose exec app ./bin/console doctrine:migrations:migrate"
echo ""

# Display access information
echo "🌐 After starting Docker containers, access the application:"
echo "  Blog: http://localhost:8000/blog/"
echo "  Assets: http://localhost:8000/assets/"
echo ""

# Display helpful commands
echo "📝 Useful commands:"
echo "  Start containers: docker-compose up -d"
echo "  Stop containers: docker-compose down"
echo "  View logs: docker-compose logs -f app"
echo "  Run console: docker-compose exec app ./bin/console"
echo ""

echo "✨ Setup complete! Follow the instructions above to get started."

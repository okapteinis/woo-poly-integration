#!/bin/bash
#
# Development Setup Script for WooCommerce Polylang Integration
#
# This script sets up the development environment for contributors.
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  WooCommerce Polylang Integration - Development Setup${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}\n"

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Error: Composer is not installed${NC}"
    echo -e "${YELLOW}Please install Composer: https://getcomposer.org/${NC}"
    exit 1
fi

# Check if git is installed
if ! command -v git &> /dev/null; then
    echo -e "${RED}Error: Git is not installed${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1/5: Installing Composer dependencies...${NC}"
composer install
echo -e "${GREEN}✓ Dependencies installed${NC}\n"

echo -e "${YELLOW}Step 2/5: Setting up Git hooks...${NC}"
if [ -f ".git-hooks/pre-commit" ]; then
    cp .git-hooks/pre-commit .git/hooks/pre-commit
    chmod +x .git/hooks/pre-commit
    echo -e "${GREEN}✓ Pre-commit hook installed${NC}\n"
else
    echo -e "${YELLOW}⚠ Pre-commit hook file not found${NC}\n"
fi

echo -e "${YELLOW}Step 3/5: Creating necessary directories...${NC}"
mkdir -p coverage logs
echo -e "${GREEN}✓ Directories created${NC}\n"

echo -e "${YELLOW}Step 4/5: Running code quality checks...${NC}"
echo -e "  - PHP CodeSniffer..."
composer cs || echo -e "${YELLOW}  ⚠ Code style issues found (not critical for setup)${NC}"
echo -e "  - PHPStan..."
composer analyze || echo -e "${YELLOW}  ⚠ Static analysis issues found (not critical for setup)${NC}"
echo -e "${GREEN}✓ Code quality checks completed${NC}\n"

echo -e "${YELLOW}Step 5/5: Running tests...${NC}"
composer test || echo -e "${YELLOW}  ⚠ Some tests failed (not critical for setup)${NC}"
echo -e "${GREEN}✓ Tests completed${NC}\n"

echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}Setup complete! 🎉${NC}\n"

echo -e "${BLUE}Next steps:${NC}"
echo -e "  1. Read CONTRIBUTING.md for development guidelines"
echo -e "  2. Check out the nightly branch: ${YELLOW}git checkout nightly${NC}"
echo -e "  3. Create a feature branch: ${YELLOW}git checkout -b feature/your-feature${NC}"
echo -e "  4. Make your changes and run: ${YELLOW}composer ci${NC}"
echo -e "  5. Submit a pull request!\n"

echo -e "${BLUE}Useful commands:${NC}"
echo -e "  ${YELLOW}composer test${NC}          - Run tests"
echo -e "  ${YELLOW}composer test:coverage${NC} - Run tests with coverage report"
echo -e "  ${YELLOW}composer cs${NC}            - Check code style"
echo -e "  ${YELLOW}composer cs:fix${NC}        - Fix code style issues"
echo -e "  ${YELLOW}composer analyze${NC}       - Run static analysis"
echo -e "  ${YELLOW}composer ci${NC}            - Run all CI checks\n"

echo -e "${GREEN}Happy coding! 💻${NC}\n"

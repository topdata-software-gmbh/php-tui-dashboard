# Contributing to PHP TUI Dashboard Framework

Thank you for your interest in contributing to the PHP TUI Dashboard Framework! This guide will help you get started.

## Getting Started

### Prerequisites

- PHP 8.1 or higher
- Composer
- Git
- A Unix-like terminal (Linux, macOS, WSL)

### Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/yourusername/php-tui-dashboard.git
   cd php-tui-dashboard
   ```
3. Install dependencies:
   ```bash
   composer install
   ```

### Running Examples

```bash
# Basic rendering test
php examples/basic.php

# Layout demonstrations
php examples/layouts.php

# Widget showcase
php examples/widgets.php

# Complete dashboard
php examples/dashboard.php

# Interactive application
php examples/interactive.php

# ETL monitor
php examples/etl-monitor.php
```

## Development Workflow

### Code Style

This project follows PSR-12 coding standards. Use the following tools:

```bash
# Fix code style
composer fix-cs

# Run static analysis
composer analyze

# Run tests
composer test
```

### Making Changes

1. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```
2. Make your changes
3. Add tests for new functionality
4. Ensure all tests pass
5. Update documentation if needed
6. Commit your changes:
   ```bash
   git commit -m "Add your feature description"
   ```
7. Push to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```
8. Create a pull request

## Project Structure

```
php-tui-dashboard/
├── src/                    # Source code
│   ├── Component.php       # Base component class
│   ├── Application.php     # Main application class
│   ├── Layout/            # Layout system
│   ├── Widget/            # Widget components
│   ├── Event/             # Event system
│   └── Input/             # Input handling
├── examples/              # Example applications
├── docs/                  # Documentation
├── tests/                 # Test suite
└── composer.json          # Dependencies
```

## Adding New Widgets

When creating a new widget:

1. Extend the `Widget` base class
2. Implement the `render()` method
3. Use `getInnerArea()` for content positioning
4. Add styling with `getStyleString()`
5. Create an example in `examples/widgets.php`
6. Add documentation

Example:

```php
<?php

namespace PhpTuiDashboard\Widget;

class MyWidget extends Widget
{
    public function render(Renderer $renderer): void
    {
        $this->renderBorder($renderer);
        
        ['position' => $pos, 'size' => $size] = $this->getInnerArea();
        
        $renderer->moveTo($pos);
        $renderer->write("My widget content");
    }
}
```

## Adding New Layouts

When creating a new layout:

1. Extend the `Layout` base class
2. Implement `calculateAreas()` method
3. Handle component positioning and sizing
4. Add examples to `examples/layouts.php`
5. Document the layout behavior

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run specific test
php vendor/bin/phpunit tests/Unit/ComponentTest.php
```

### Writing Tests

- Unit tests for individual classes
- Integration tests for component interactions
- Performance tests for rendering
- Use descriptive test names
- Test edge cases and error conditions

Example:

```php
<?php

namespace PhpTuiDashboard\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpTuiDashboard\Position;
use PhpTuiDashboard\Size;

class PositionTest extends TestCase
{
    public function testTranslate(): void
    {
        $pos = new Position(5, 10);
        $translated = $pos->translate(2, 3);
        
        $this->assertEquals(7, $translated->x);
        $this->assertEquals(13, $translated->y);
    }
}
```

## Documentation

### Updating Documentation

- README.md for overview and quick start
- docs/getting-started.md for detailed guide
- Inline PHPDoc for API documentation
- Update examples when adding features

### Documentation Style

- Use clear, concise language
- Include code examples
- Explain concepts before showing code
- Use consistent formatting

## Performance Guidelines

- Use differential rendering
- Minimize object allocation in hot paths
- Cache expensive calculations
- Profile with large datasets

## Submitting Pull Requests

### PR Checklist

- [ ] Code follows PSR-12 standards
- [ ] Tests pass for new functionality
- [ ] Documentation is updated
- [ ] Examples work correctly
- [ ] No breaking changes (or clearly documented)

### PR Description

Include:
- Problem being solved
- Approach taken
- Testing performed
- Any breaking changes

## Getting Help

- Check existing issues and pull requests
- Read the documentation
- Run examples to understand usage
- Ask questions in issues

## Release Process

1. Update version in composer.json
2. Update CHANGELOG.md
3. Create git tag
4. Create GitHub release
5. Publish to Packagist

Thank you for contributing! 🎉

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial implementation of PHP TUI Dashboard Framework
- Component-based architecture with flexible layout system
- Built-in widgets: LogWidget, ProgressBar, SystemMetricsWidget, FooterBar
- Event-driven application framework with keyboard input handling
- Differential rendering for optimal performance
- Comprehensive examples and documentation

### Features
- **Core Architecture**
  - Abstract Component base class
  - Position, Size, and Area value objects
  - SoloScreenRenderer for terminal output
  - Renderer interface for pluggable rendering

- **Layout System**
  - FlexLayout for row/column arrangements
  - GridLayout for 2D tile layouts
  - Automatic component positioning and sizing
  - Support for fixed and flexible constraints

- **Widget System**
  - Widget base class with border and styling
  - LogWidget with filtering and scrolling
  - ProgressBar with segments and labels
  - SystemMetricsWidget with gauges
  - FooterBar with sectioned layout

- **Application Framework**
  - Application class with event loop
  - EventDispatcher for event handling
  - InputHandler for keyboard processing
  - Real-time update callbacks
  - Configurable FPS and differential rendering

- **Examples**
  - Basic rendering demonstration
  - Layout system examples
  - Widget showcase
  - Complete ETL dashboard
  - Interactive application
  - ETL monitoring application

### Documentation
- Comprehensive README with quick start guide
- Getting started guide with detailed examples
- Contribution guidelines
- MIT License

## [1.0.0] - 2026-03-11

### Added
- Complete PHP TUI Dashboard Framework implementation
- Full widget library for dashboard applications
- Event-driven architecture for interactive applications
- Performance-optimized differential rendering
- ETL-focused monitoring capabilities

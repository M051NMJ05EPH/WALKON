-- Seed Platform Features Table
TRUNCATE TABLE platform_features;

INSERT INTO platform_features (title, description, icon) VALUES
('Multi-Channel Sync', 'Seamlessly sync inventory across all marketplaces in 5 minutes. No more overselling.', 'fas fa-layer-group'),
('Smart Analytics', 'Real-time profit tracking, sales insights, and predictive demand modeling.', 'fas fa-chart-line'),
('Auto-Pricing', 'Stay competitive with automated repricing strategies that protect your margins.', 'fas fa-bolt'),
('Universal Database', 'Consolidate all your product data into a single, high-performance universal catalog.', 'fas fa-database');

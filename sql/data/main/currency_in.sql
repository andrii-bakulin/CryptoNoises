-- CryptoNoises: reference data for `currency_in` (main database). Load with: sql/data/load.sh main <database>

INSERT INTO `currency_in` (`currency_in_id`, `coin_gecko_id`, `name`, `is_enabled`, `is_public`, `exchange_normalize_mult`, `exchange_normalize_div`, `order_index`) VALUES
  ('ADA', 'cardano', 'Cardano', '1', '1', '1', '1', '60000'),
  ('BTC', 'bitcoin', 'Bitcoin', '1', '1', '1', '1', '90000'),
  ('ETH', 'ethereum', 'Ethereum', '1', '1', '1', '1', '80000'),
  ('MATIC', 'matic-network', 'Polygon', '1', '1', '1', '1', '50000'),
  ('SOL', 'solana', 'Solana', '1', '1', '1', '1', '70000'),
  ('XTZ', 'tezos', 'Tezos', '1', '1', '1', '1000000', '40000');

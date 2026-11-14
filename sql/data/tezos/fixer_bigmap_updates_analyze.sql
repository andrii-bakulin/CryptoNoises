-- CryptoNoises: reference data for `fixer_bigmap_updates_analyze` (tezos database). Load with: sql/data/load.sh tezos <database>

-- Start point of `run-tezos /analyze/bigmap-updates/new`: an 'idle' row is the next level to analyze (here block 1).
-- It waits until /blockchain/bigmap-updates/sync-new has synced that level.
INSERT INTO `fixer_bigmap_updates_analyze` (`level`, `state`) VALUES
  (1, 'idle');

-- CryptoNoises: reference data for `fixer_bigmap_updates_sync` (tezos database). Load with: sql/data/load.sh tezos <database>

-- Start point of `run-tezos /blockchain/bigmap-updates/sync-new`: an 'idle' row is the next level to sync (here block 1).
INSERT INTO `fixer_bigmap_updates_sync` (`level`, `state`) VALUES
  (1, 'idle');

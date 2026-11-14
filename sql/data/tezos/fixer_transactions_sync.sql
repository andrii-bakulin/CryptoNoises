-- CryptoNoises: reference data for `fixer_transactions_sync` (tezos database). Load with: sql/data/load.sh tezos <database>

-- Start point of `run-tezos /blockchain/transaction/sync-new`: an 'idle' row is the next level to sync (here block 1).
-- Keep it in line with config.sql (TezosAnalyzer.LastSyncedLevel = start level - 1).
INSERT INTO `fixer_transactions_sync` (`level`, `state`) VALUES
  (1, 'idle');

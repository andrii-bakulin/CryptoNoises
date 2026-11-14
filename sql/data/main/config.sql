-- CryptoNoises: reference data for `config` (main database). Load with: sql/data/load.sh main <database>

-- command-lock.*: manual pause switch per command: '0' = enabled, any other value = the command exits ("Command locked!").
-- Not seeded (set them for your own Discord server if needed):
--   discord.stats.main.channel-id: channel where the Discord bot keeps a message with indexer stats (no key = disabled);
--   discord.stats.main.message-id: id of that message, written by the bot itself.
INSERT INTO `config` (`cfg_key`, `cfg_value`) VALUES
  ('command-lock.coin-gecko.sync', '0'),
  ('command-lock.events-tokens-mint.tezos', '0'),
  ('command-lock.events-tokens-updates.tezos', '0'),
  ('command-lock.job.domains-expiry', '0'),
  ('command-lock.lifecycles.check-error-logs', '0'),
  ('command-lock.notifier.events-flow', '0'),
  ('command-lock.notifier.inform-flow', '0'),
  ('command-lock.tezos-network.analyze-new-transactions', '0'),
  ('command-lock.tezos-network.sync-wallets-states', '0');

-- CryptoNoises: reference data for `address_by_creators` (tezos database). Load with: sql/data/load.sh tezos <database>

-- `hits` is a runtime counter and is not seeded (starts from its DEFAULT)
INSERT INTO `address_by_creators` (`creator_name`, `address`, `notice`) VALUES
  ('Cordae x Shay the Surrelest', 'tz1i8D4DHkuQsGgpxEaHPRsdYtKCFMnmeN6Z', NULL),
  ('DOGAMI', 'tz1bq5QazD43hBGxYxX8mv4sa1r1kz5sRGWz', NULL),
  ('FanLive', 'tz000000000000000000000000000Z000002', 'https://www.fanlivecards.com/'),
  ('Maverickz_Artz, Kaiser K9 Omega, VivekasCoder, Meka Prime, Jo143, Cyber Togo', 'tz2ANxo2gjHxkxAv5cLW8utqNi1SFdXkZ4v5', NULL),
  ('SocialSweet Inc.', 'tz000000000000000000000000000Z000001', 'https://sweet.io/'),
  ('gifdotgames', 'tz1bKM4FRgAsGdDWzXs4o5HZdjBbLMbPBAA1', NULL);

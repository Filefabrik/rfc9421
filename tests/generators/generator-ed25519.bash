#
# Copyright (c) 2026.
# e-mail: rt@filefabrik.com
# mastodon: filefabrik@phpc.social
# License: proprietary
# State: under development
#

# https://mojoauth.com/security-guides/generate-keypair-using-ed25519-with-shell#how-to-generate-an-ed25519-ssh-key-with-ssh-keygen
openssl genpkey -algorithm ed25519 -out ../Keys/ed25519-private.pem
openssl pkey -in ../Keys/ed25519-private.pem -pubout -out ../Keys/ed25519-public.pem

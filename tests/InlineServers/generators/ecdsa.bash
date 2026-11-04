#
# Copyright (c) 2026.
# e-mail: rt@filefabrik.com
# mastodon: filefabrik@phpc.social
# License: proprietary
# State: under development
#

openssl genpkey -algorithm EC -pkeyopt ec_paramgen_curve:P-256 -out ../Keys/ecdsaP256-private.pem
openssl pkey -in ../Keys/ecdsaP256-private.pem -pubout -out ../Keys/ecdsaP256-public.pem

openssl genpkey -algorithm EC -pkeyopt ec_paramgen_curve:P-384 -out ../Keys/ecdsaP384-private.pem
openssl pkey -in ../Keys/ecdsaP384-private.pem -pubout -out ../Keys/ecdsaP384-public.pem

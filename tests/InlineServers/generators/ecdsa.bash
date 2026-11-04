#
# Copyright (c) 2026.  rt@filefabrik.com
# proprietary
# todo insert correct copyright
#

openssl genpkey -algorithm EC -pkeyopt ec_paramgen_curve:P-256 -out ../Keys/ecdsaP256-private.pem
openssl pkey -in ../Keys/ecdsaP256-private.pem -pubout -out ../Keys/ecdsaP256-public.pem

openssl genpkey -algorithm EC -pkeyopt ec_paramgen_curve:P-384 -out ../Keys/ecdsaP384-private.pem
openssl pkey -in ../Keys/ecdsaP384-private.pem -pubout -out ../Keys/ecdsaP384-public.pem

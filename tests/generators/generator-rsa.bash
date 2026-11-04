#
# Copyright (c) 2026.
# e-mail: rt@filefabrik.com
# mastodon: filefabrik@phpc.social
# License: proprietary
# State: under development
#

openssl genrsa -out ../Keys/rsa-private.pem 4096
openssl rsa -in ../Keys/rsa-private.pem -pubout -out ../Keys/rsa-public.pem

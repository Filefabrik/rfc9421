#
# Copyright (c) 2026.  rt@filefabrik.com
# proprietary
# todo insert correct copyright
#

head -c 32 /dev/urandom | base64 > ../Keys/hmac-private.pem > ../Keys/hmac-public.pem

openssl genrsa -out ../Keys/rsa-private.pem 4096
openssl rsa -in ../Keys/rsa-private.pem -pubout -out ../Keys/rsa-public.pem

echo Generating SSL cert for php.template.local...
mkcert -install
mkcert -cert-file ./dev/certs/local.crt ^
       -key-file ./dev/certs/local.key ^
       php.template.local 127.0.0.1 ::1

docker-compose up --build 
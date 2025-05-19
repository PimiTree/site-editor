@echo off
echo Generating SSL cert for editor.local...
mkcert -install
mkcert -cert-file ./dev/certs/editor.local.crt ^
       -key-file ./dev/certs/editor.local.key ^
       editor.local 127.0.0.1 ::1

docker-compose up --build -d
 sudo docker build -t worker .
 sudo docker run --add-host host.docker.internal:host-gateway worker

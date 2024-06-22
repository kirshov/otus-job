namespace = kirshov-otus
start: init install-all
init: 
	kubectl get namespace | grep "^$(namespace)" || kubectl create namespace $(namespace)
	helm repo add ingress-nginx https://kubernetes.github.io/ingress-nginx/
	helm repo update
	helm upgrade --install nginx ingress-nginx/ingress-nginx -f "./.helm/nginx-ingress/values.yaml"
	kubectl apply -f https://raw.githubusercontent.com/rancher/local-path-provisioner/master/deploy/local-path-storage.yaml
	helm upgrade --install redis oci://registry-1.docker.io/bitnamicharts/redis -f "./.helm/redis/values.yaml" -n $(namespace)

mk-start: 
	minikube start
	minikube tunnel

mk-stop: 
	minikube stop

install-all: install-users install-store

install-users:
	#helm upgrade --install app-users-db oci://registry-1.docker.io/bitnamicharts/postgresql -f  "./app-users/chart/postgres.yaml"  -n $(namespace)
	helm upgrade --install app-users "./app-users/chart" -n $(namespace)

install-store:
	#helm upgrade --install app-store-db oci://registry-1.docker.io/bitnamicharts/postgresql -f  "./app-store/chart/postgres.yaml"  -n $(namespace)
	helm upgrade --install app-store "./app-store/chart" -n $(namespace)

install-orders:
	#helm upgrade --install app-orders-db oci://registry-1.docker.io/bitnamicharts/postgresql -f  "./app-orders/chart/postgres.yaml"  -n $(namespace)
	helm upgrade --install app-orders "./app-orders/chart" -n $(namespace)

delete-namespace:
	kubectl delete namespace $(namespace)

uninstall-users:
	helm uninstall app-users -n $(namespace)
	#helm uninstall app-users-db -n $(namespace)
	#kubectl delete persistentvolumeclaim data-app-users-db-postgresql-0 -n $(namespace)

uninstall-store:
	helm uninstall app-store -n $(namespace)
	#helm uninstall app-store-db -n $(namespace)
	#kubectl delete persistentvolumeclaim data-app-store-db-postgresql-0 -n $(namespace)

uninstall-orders:
	helm uninstall app-orders -n $(namespace)
	#helm uninstall app-orders-db -n $(namespace)
	#kubectl delete persistentvolumeclaim data-app-orders-db-postgresql-0 -n $(namespace)

uninstall-all: uninstall-users uninstall-store delete-namespace

port-forward:
	kubectl port-forward --namespace kirshov-otus svc/app-users-db-postgresql 5431:5432 & \
    kubectl port-forward --namespace kirshov-otus svc/app-store-db-postgresql 5433:5432 & \
    kubectl port-forward --namespace kirshov-otus svc/redis-master 6379:6379
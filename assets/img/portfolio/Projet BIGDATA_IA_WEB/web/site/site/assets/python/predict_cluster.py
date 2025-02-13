try:
	import pandas as pd
	import pymysql
	import os
	import argparse
	from sklearn.cluster import KMeans, AgglomerativeClustering
except Exception as e:
	print(e)


parser = argparse.ArgumentParser(description="Predicts clusters of trees", formatter_class=argparse.ArgumentDefaultsHelpFormatter)
parser.add_argument("model", help="Model to use for the prediction", type=str)
parser.add_argument("clust", help="Number of clusters to use for the prediction", type=int)
args = parser.parse_args()


def cluster(data):
	"""
	Applique le clustering sur les données et retourne une carte interactive des résultats.

	Parameters:
	data (pd.DataFrame): Le dataframe contenant les données avec les colonnes 'latitude', 'longitude', et 'haut_tot'.
	n_clusters (int): Le nombre de clusters à créer.
	method (str): La méthode de clustering à utiliser ('kmeans' ou 'agglomerative').

	Returns:
	fig (plotly.graph_objs._figure.Figure): La figure Plotly de la carte interactive.
	"""
	method = args.model
	n_clusters = args.clust
	if method == 'kmeans':
		model = KMeans(n_clusters=n_clusters, random_state=0)
	elif method == 'agglomerative':
		model = AgglomerativeClustering(n_clusters=n_clusters)
	else:
		raise ValueError("Méthode de clustering non reconnue. Utilisez 'kmeans' ou 'agglomerative'.")

	# Appliquer le clustering
	data['cluster'] = model.fit_predict(data[['haut_tot']])

	return data

def main(conn):
	try:
		with conn.cursor() as cursor:
			sql = "SELECT * FROM `arbre`;"
			cursor.execute(sql)
			data = cursor.fetchall()
		df = pd.DataFrame.from_dict(data, orient="columns")
		df = df[['latitude', 'longitude', 'haut_tot']].dropna()
	except Exception as e:
		print(e)

	# Générer les cluster
	data = cluster(df)

	try:
		print(data.to_json(orient="records"))
	except Exception as e:
		print(e)

if __name__ == "__main__":
	conn = pymysql.connect(
		host="localhost",
		user="etu0115",
		password="ygxmpljt",
		db="etu0115",
		charset="utf8mb4",
		cursorclass=pymysql.cursors.DictCursor
	)
	try:
		main(conn)
	finally:
		conn.close()

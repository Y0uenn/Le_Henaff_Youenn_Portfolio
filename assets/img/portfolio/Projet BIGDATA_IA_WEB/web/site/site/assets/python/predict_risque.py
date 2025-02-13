try:
	import pandas as pd
	import pymysql
	import folium
	from sklearn.preprocessing import StandardScaler, LabelEncoder
	import joblib
	import os
	import argparse
	import warnings
except Exception as e:
	print(e)
warnings.filterwarnings("ignore")


parser = argparse.ArgumentParser(description="Predicts age with given JSON", formatter_class=argparse.ArgumentDefaultsHelpFormatter)
parser.add_argument("id", help="ID of tree", type=int)
parser.add_argument("model", help="Model to use for the prediction", type=str)
args = parser.parse_args()


def afficher_carte_arbre_deracine(data, filepath):
	method = args.model

	loaded_model = joblib.load(os.path.join(filepath+f'/../pkl/best_{method}_Regressor.pkl'))

	# Charger les données depuis un fichier JSON
	loaded_scaler = joblib.load(os.path.join(filepath+'/../pkl/scaler.pkl'))
	loaded_label_encoder_arb_etat = joblib.load(os.path.join(filepath+'/../pkl/label_encoder_fk_arb_etat.pkl'))
	categorical_columns = ['fk_prec_estim', 'fk_stadedev']
	loaded_label_encoders = {column: joblib.load(os.path.join(filepath+f'/../pkl/label_encoder_{column}.pkl')) for column in categorical_columns}

	# Sélectionner les colonnes pertinentes
	columns_of_interest = ['haut_tot', 'tronc_diam', 'age_estim', 'fk_prec_estim', 'clc_nbr_diag', 'fk_stadedev', 'latitude', 'longitude']

	# Encodage des colonnes catégorielles avec gestion des valeurs inconnues
	for column in categorical_columns:
		le = loaded_label_encoders[column]
		data[column] = data[column].apply(lambda x: le.transform([x])[0] if x in le.classes_ else -1)

	# Normalisation des données
	features = ['haut_tot', 'tronc_diam', 'age_estim', 'fk_prec_estim', 'clc_nbr_diag', 'fk_stadedev']
	X_new = data[features]

	# Vérifier que les colonnes correspondent exactement
	expected_feature_names = loaded_scaler.feature_names_in_
	X_new = X_new[expected_feature_names]

	X_new_scaled = loaded_scaler.transform(X_new)

	# Prédiction
	y_new_pred = loaded_model.predict(X_new_scaled)

	print({"risque": y_new_pred[0]}, end="")

def main(conn):
	try:
		with conn.cursor() as cursor:
			sql = f"SELECT * FROM `arbre` WHERE id={args.id};"
			cursor.execute(sql)
			data = cursor.fetchall()
		df = pd.DataFrame.from_dict(data, orient="columns")
		df = df[['id', 'haut_tot', 'tronc_diam', 'age_estim', 'fk_prec_estim', 'clc_nbr_diag', 'fk_stadedev']].dropna()
	except Exception as e:
		print(e)
	afficher_carte_arbre_deracine(df, os.path.dirname(os.path.abspath(__file__)))

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

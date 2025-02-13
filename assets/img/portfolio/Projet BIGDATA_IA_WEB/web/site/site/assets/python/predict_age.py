import pandas as pd
import joblib
import json
import os
import argparse
import warnings
warnings.filterwarnings("ignore")


parser = argparse.ArgumentParser(description="Predicts age with given JSON", formatter_class=argparse.ArgumentDefaultsHelpFormatter)
parser.add_argument("json", help="Input JSON", type=str)
parser.add_argument("model", help="Model to use for the prediction", type=str)
args = parser.parse_args()


def load_model_and_scalers(filepath):
	model = args.model
	model = joblib.load(os.path.join(filepath + f"/../pkl/best_{model}.pkl"))
	scaler_X = joblib.load(os.path.join(filepath + "/../pkl/scaler_X.pkl"))
	scaler_y = joblib.load(os.path.join(filepath + "/../pkl/scaler_y.pkl"))
	return model, scaler_X, scaler_y

def predict_age(json_input, filepath):
	# Charger les données de test depuis le JSON
	data_test = pd.read_json(json_input)

	# Charger le modèle et les scalers sauvegardés
	model, scaler_X, scaler_y = load_model_and_scalers(filepath)

	# Normaliser les données d"entrée
	X_test_scaled = scaler_X.transform(data_test)

	# Faire des prédictions
	y_pred_scaled = model.predict(X_test_scaled)

	# Inverser la normalisation des prédictions
	y_pred = scaler_y.inverse_transform(y_pred_scaled.reshape(-1, 1))

	# Convertir les résultats en JSON
	predictions = pd.DataFrame(y_pred, columns=["age_predicted"])
	result_json = predictions.to_json(orient="records", indent=4)
	return result_json

if __name__ == "__main__":
	json_input = "["+args.json+"]"
	script_dir = os.path.dirname(os.path.abspath(__file__))
	result_json = predict_age(json_input, script_dir)
	print(result_json)

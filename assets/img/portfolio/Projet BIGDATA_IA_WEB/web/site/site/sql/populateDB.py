#!/usr/bin/python3
import pandas as pd
import pymysql # https://www.freecodecamp.org/news/how-to-read-and-write-data-to-a-sql-database-using-python/
import os
import math
from pyproj import Transformer # Change projection: https://gis.stackexchange.com/a/78944


cold_columns = [
	"clc_quartier",
	"clc_secteur",
	"fk_arb_etat",
	"fk_stadedev",
	"fk_port",
	"fk_pied",
	"fk_situation",
	"fk_nomtech",
	"villeca",
	"feuillage"
]

def load_data(csv_path):
	df = pd.read_csv(csv_path, delimiter=",")
	return df

def write_cold_entries(conn, names, data):
	for name in names:
		for entry in data[name].unique():
			if type(entry) is str and (entry.lower() == "oui" or entry.lower() == "non"):
				val = entry.lower() == "oui"
			else:
				if type(entry) in (float, int) and math.isnan(entry):
					val = "unknown"
				else:
					val = entry
			print(name, val)
			conn.cursor().execute(f"INSERT INTO `{name}` VALUES (%s);", (val))
	conn.commit()

def add_trees(conn, data):
	base_stmt = "INSERT INTO `arbre` (`id`, `longitude`, `latitude`, `clc_quartier`, `clc_secteur`, `haut_tot`, `haut_tronc`, `tronc_diam`, `fk_arb_etat`, `fk_stadedev`, `fk_port`, `fk_pied`, `fk_situation`, `fk_revetement`, `age_estim`, `fk_prec_estim`, `clc_nbr_diag`, `fk_nomtech`, `villeca`, `feuillage`, `remarquable`) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s);"
	transformer = Transformer.from_crs("EPSG:3949", "EPSG:4326")
	for index, row in data.iterrows():
		stmt = base_stmt
		args = list()
		for name in data.columns:
			if name in ("Y", "commentaire_environnement", "dte_plantation", "dte_abattage", "src_geo"):
				continue
			if name in ("fk_revetement", "remarquable"):
				val = str(row[name])
				if val.lower() in ("false", "non"):
					val = 0
				elif val.lower() in ("true", "oui"):
					val = 1
				else:
					val = 0
			elif name == "X":
				val = transformer.transform(row["X"], row["Y"])
			else:
				val = row[name]
				if str(val).lower() in ("na", "nan"):
					val = None
				if name not in cold_columns and val is None:
					val = 0
			if val == "NULL" or type(val) is not str:
				if name == "X":
					args.append(val[1]) # is backwards for some reason
					args.append(val[0])
				else:
					args.append(val)
			else:
				args.append(val)
		conn.cursor().execute(stmt, args)
	conn.commit()

conn = pymysql.connect(
	host="localhost",
	user="etu0115",
	password="ygxmpljt",
	db="etu0115",
	charset="utf8mb4",
	cursorclass=pymysql.cursors.DictCursor
)
script_dir = os.path.dirname(os.path.abspath(__file__))
data = load_data(os.path.join(script_dir, "Data_Arbre.csv"))
try:
	write_cold_entries(conn, cold_columns, data)
	add_trees(conn, data)
finally:
	conn.close()

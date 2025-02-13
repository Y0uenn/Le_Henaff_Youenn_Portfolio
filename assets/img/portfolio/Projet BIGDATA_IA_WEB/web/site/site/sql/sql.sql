#------------------------------------------------------------
#        Script MySQL.
#------------------------------------------------------------


#------------------------------------------------------------
# Table: clc_quartier
#------------------------------------------------------------

CREATE TABLE clc_quartier(
        clc_quartier Varchar (50) NOT NULL
	,CONSTRAINT clc_quartier_PK PRIMARY KEY (clc_quartier)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: clc_secteur
#------------------------------------------------------------

CREATE TABLE clc_secteur(
        clc_secteur Varchar (100) NOT NULL
	,CONSTRAINT clc_secteur_PK PRIMARY KEY (clc_secteur)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: fk_stadedev
#------------------------------------------------------------

CREATE TABLE fk_stadedev(
        fk_stadedev Varchar (20) NOT NULL
	,CONSTRAINT fk_stadedev_PK PRIMARY KEY (fk_stadedev)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: fk_arb_etat
#------------------------------------------------------------

CREATE TABLE fk_arb_etat(
        fk_arb_etat Varchar (20) NOT NULL
	,CONSTRAINT fk_arb_etat_PK PRIMARY KEY (fk_arb_etat)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: fk_port
#------------------------------------------------------------

CREATE TABLE fk_port(
        fk_port Varchar (50) NOT NULL
	,CONSTRAINT fk_port_PK PRIMARY KEY (fk_port)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: fk_pied
#------------------------------------------------------------

CREATE TABLE fk_pied(
        fk_pied Varchar (50) NOT NULL
	,CONSTRAINT fk_pied_PK PRIMARY KEY (fk_pied)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: fk_situation
#------------------------------------------------------------

CREATE TABLE fk_situation(
        fk_situation Varchar (50) NOT NULL
	,CONSTRAINT fk_situation_PK PRIMARY KEY (fk_situation)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: fk_nomtech
#------------------------------------------------------------

CREATE TABLE fk_nomtech(
        fk_nomtech Varchar (50) NOT NULL
	,CONSTRAINT fk_nomtech_PK PRIMARY KEY (fk_nomtech)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: feuillage
#------------------------------------------------------------

CREATE TABLE feuillage(
        feuillage Varchar (50) NOT NULL
	,CONSTRAINT feuillage_PK PRIMARY KEY (feuillage)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: villeca
#------------------------------------------------------------

CREATE TABLE villeca(
        villeca Varchar (50) NOT NULL
	,CONSTRAINT villeca_PK PRIMARY KEY (villeca)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: arbre
#------------------------------------------------------------

CREATE TABLE arbre(
        id            Int  Auto_increment  NOT NULL ,
        longitude     Float NOT NULL ,
        latitude      Float NOT NULL ,
        haut_tot      Int NOT NULL ,
        haut_tronc    Int NOT NULL ,
        tronc_diam    Int NOT NULL ,
        fk_revetement Bool NOT NULL ,
        age_estim     Int ,
        fk_prec_estim Int NOT NULL ,
        clc_nbr_diag  Int NOT NULL ,
        remarquable   Bool NOT NULL ,
        clc_quartier  Varchar (50) ,
        clc_secteur   Varchar (100) ,
        fk_arb_etat   Varchar (20) ,
        fk_stadedev   Varchar (20) ,
        fk_port       Varchar (50) ,
        fk_situation  Varchar (50) ,
        fk_pied       Varchar (50) ,
        fk_nomtech    Varchar (50) ,
        villeca       Varchar (50) ,
        feuillage     Varchar (50)
	,CONSTRAINT arbre_PK PRIMARY KEY (id)

	,CONSTRAINT arbre_clc_quartier_FK FOREIGN KEY (clc_quartier) REFERENCES clc_quartier(clc_quartier)
	,CONSTRAINT arbre_clc_secteur0_FK FOREIGN KEY (clc_secteur) REFERENCES clc_secteur(clc_secteur)
	,CONSTRAINT arbre_fk_arb_etat1_FK FOREIGN KEY (fk_arb_etat) REFERENCES fk_arb_etat(fk_arb_etat)
	,CONSTRAINT arbre_fk_stadedev2_FK FOREIGN KEY (fk_stadedev) REFERENCES fk_stadedev(fk_stadedev)
	,CONSTRAINT arbre_fk_port3_FK FOREIGN KEY (fk_port) REFERENCES fk_port(fk_port)
	,CONSTRAINT arbre_fk_situation4_FK FOREIGN KEY (fk_situation) REFERENCES fk_situation(fk_situation)
	,CONSTRAINT arbre_fk_pied5_FK FOREIGN KEY (fk_pied) REFERENCES fk_pied(fk_pied)
	,CONSTRAINT arbre_fk_nomtech6_FK FOREIGN KEY (fk_nomtech) REFERENCES fk_nomtech(fk_nomtech)
	,CONSTRAINT arbre_villeca7_FK FOREIGN KEY (villeca) REFERENCES villeca(villeca)
	,CONSTRAINT arbre_feuillage8_FK FOREIGN KEY (feuillage) REFERENCES feuillage(feuillage)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: utilisateur
#------------------------------------------------------------

CREATE TABLE utilisateur(
        mail Varchar (255) NOT NULL ,
        nom  Varchar (255) NOT NULL ,
        mdp  Varchar (255) NOT NULL
	,CONSTRAINT utilisateur_PK PRIMARY KEY (mail)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: tokens
#------------------------------------------------------------

CREATE TABLE tokens(
        token    Varchar (64) NOT NULL ,
        creation Datetime ,
        mail     Varchar (255) NOT NULL
	,CONSTRAINT tokens_PK PRIMARY KEY (token)

	,CONSTRAINT tokens_utilisateur_FK FOREIGN KEY (mail) REFERENCES utilisateur(mail)
	,CONSTRAINT tokens_utilisateur_AK UNIQUE (mail)
)ENGINE=InnoDB;



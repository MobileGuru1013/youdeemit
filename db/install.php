<?php

require 'demetra.php';

$conn = demetra_connect();
/*
// create tables
demetra_query($conn,'drop table if exists motivation');
demetra_query($conn,'create table motivation ( id int auto_increment primary key not null, name varbinary(255) not null unique, value int, label_it varchar (255 ), label_en varchar (255 ), descr_it varchar (255 ), descr_en varchar (255 ) ) character set utf8');

demetra_query($conn,'drop table if exists domain');
demetra_query($conn,'create table domain ( id bigint auto_increment primary key not null, name varbinary (512) ) character set utf8');

demetra_query($conn,'drop table if exists page');
demetra_query($conn,'create table page ( id bigint auto_increment primary key not null, domain_id bigint not null references domain (id), uri varbinary (512) not null, unique index (domain_id,uri) ) character set utf8');

demetra_query($conn,'drop table if exists rating');
demetra_query($conn,'drop table if exists p_rating');
demetra_query($conn,'create table p_rating ( id bigint auto_increment primary key not null, motivation_id int references motivation (id), value int not null, target_id bigint not null references page (id), date timestamp, source_ip varchar (15), session_id varchar(32), note mediumtext, note_lang varchar(5), index (session_id,target_id) ) character set utf8');

demetra_query($conn,'drop table if exists d_rating');
demetra_query($conn,'create table d_rating ( id bigint auto_increment primary key not null, motivation_id int references motivation (id), value int not null, target_id bigint not null references domain (id), date timestamp, source_ip varchar (15), session_id varchar(32), note mediumtext, note_lang varchar(5), index (session_id,target_id) ) character set utf8');


// insert demetra descriptors
$motivations=array(
	// GIALLI
	'disinfo'=>array(
		'Disinformazione',
		'le informazioni presentate non sono chiare e/o dimostrabili; informazioni di scarsa qualità',
		'Disinformation',
		'infromation isn\'t clear and/or unprooved; low quality information',
		2
	),
	'misunder'=>array(
		'Fraintendibile',
		'contenuti volutamente ambigui',
		'Misunderstandable',
		'deliberately ambiguous content',
		2
	),
	'offensive'=>array(
		'Offensivo',
		'contentui che possono offendere i valori o le tradizioni dell’individuo o di un gruppo di individui',
		'Offensive',
		'content that might offend the values of individuals or groups of individuals',
		2
	),
	'demagogic'=>array(
		'Demagogico',
		'ricerca il consenso puntando sull\'emotività e non sulla ragione',
		'Demagogic',
		'seeks consensus basing on emotionality instead of reason',
		2
	),
	'discriminatory'=>array(
		'Discriminatorio',
		'presenta opinioni e comportamenti che si fondano su pregiudizi',
		'Discriminatory',
		'presents opinions or behaviours based on prejudices',
		2
	),
	'addictive'=>array(
		'Crea dipendenza',
		'richiede, induce o incentiva una frequentazione eccessiva',
		'Addictive',
		'requests, induces or incentivates an excessive attendance',
		2
	),
	'violence'=>array(
		'Violenza',
		'immagini, video o testi con scene violente',
		'Violence',
		'pictures, viedeos or texts with violent scenes',
		3
	),
	'pedoporno'=>array(
		'Pedo/pornografia',
		'immagini, video o contenuti testuali pedo/pornografici',
		'Violence',
		'pedo/pornographic pictures, videos or texts',
		3
	),
	'humanrights'=>array(
		'Contro i diritti umani',
		'culti violenti, pratiche pericolose, droghe, armi',
		'Against human rights',
		'Violent worships, dangerous practices, drugs, weapons',
		3
	),
	'discriminatory+'=>array(
		'Discriminatorio',
		'presenta opinioni e comportamenti che si fondano su pregiudizi',
		'Discriminatory',
		'presents opinions or behaviours based on prejudices',
		3
	),
	'crime'=>array(
		'Criminalità',
		'induce o incentiva comportamenti criminosi',
		'Crime',
		'induces or incentivates criminal behaviour',
		3
	),
	'prostitution'=>array(
		'Prostituzione',
		'induce o incentiva la prostituzione e/o il suo sfruttamento',
		'Prostitution',
		'induces or incentivates prostitution or its exploitation',
		3
	),
	'gambling'=>array(
		'Gioco d\'azzardo',
		'incentiva il gioco d\'azzardo',
		'Violence',
		'incentivates gambling',
		3
	),
	'fraud'=>array(
		'Truffa',
		'i concetti o le proposte presenti sulla pagina sono mendaci e mirano a convincere l’utente a compiere azioni contro il suo interesse',
		'Fraud',
		'the concepts or proposals presented are mendacious and seek to convince the user to accomplish actions against his/her own interest',
		3
	)
);
foreach( $motivations as $n=>$o ) {
	demetra_query($conn,'insert into motivation (name,value,label_it,descr_it,label_en,descr_en) values ( '.
		demetra_quote($conn,$n).','.
		demetra_quote($conn,$o[4]).','.
		demetra_quote($conn,$o[0]).','.
		demetra_quote($conn,$o[1]).','.
		demetra_quote($conn,$o[2]).','.
		demetra_quote($conn,$o[3]).
	')' );
}



demetra_query($conn,'drop table if exists user');
demetra_query($conn,'create table user ( id int auto_increment primary key not null, nickname varbinary(255) not null unique, email varbinary(255) not null unique, password varbinary(255) not null, lang varbinary(2) ) character set utf8' );
demetra_query($conn,'drop table if exists pending_user');
demetra_query($conn,'create table pending_user ( id int auto_increment primary key not null, nickname varbinary(255) not null unique, email varbinary(255) not null unique, password varbinary(255) not null, lang varbinary(2), code varbinary(10) not null unique ) character set utf8' );

demetra_query($conn,'alter table p_rating add column user_id int' );
demetra_query($conn,'alter table d_rating add column user_id int' );
*/
demetra_close($conn);

?>

/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     06/11/2025 14:21:35                          */
/*==============================================================*/

drop table if exists patient_professional_link;
drop table if exists chat_log;
drop table if exists emotional_diary;
drop table if exists patient_profile;
drop table if exists professional_notes;
drop table if exists professional_profile;
drop table if exists resource_hub;
drop table if exists tic_log;
drop table if exists track_medicine;
drop table if exists user_profile;

/*==============================================================*/
/* Table: patient_professional_link                             */
/*==============================================================*/
CREATE TABLE patient_professional_link (
    Link_ID INT(11) NOT NULL AUTO_INCREMENT,
    Patient_ID INT(11) NOT NULL,
    Professional_ID INT(11) NOT NULL,
    Assigned_Date DATE NOT NULL DEFAULT CURDATE(),
    PRIMARY KEY (Link_ID),

    CONSTRAINT fk_ppl_patient FOREIGN KEY (Patient_ID) 
        REFERENCES patient_profile(PATIENT_ID)
        ON DELETE RESTRICT ON UPDATE RESTRICT,

    CONSTRAINT fk_ppl_professional FOREIGN KEY (Professional_ID) 
        REFERENCES professional_profile(PROFESSIONAL_ID)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*==============================================================*/
/* Table: chat_log                                              */
/*==============================================================*/
create table chat_log
(
   CHAT_ID              bigint unsigned not null,
   USE_USER_ID          bigint,
   PROFESSIONAL_ID      int,
   PAT_USE_USER_ID      bigint,
   PATIENT_ID           int,
   SENDER_ID            int not null,
   RECEIVER_ID          int not null,
   CHAT_TEXT            text not null,
   CHAT_TIME            datetime not null,
   primary key (CHAT_ID)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*==============================================================*/
/* Table: emotional_diary                                       */
/*==============================================================*/
create table emotional_diary
(
   EMOTIONAL_DIARY_ID   int not null,
   USE_USER_ID          bigint,
   PATIENT_ID           int not null,
   OCURRENCE            datetime not null,
   EMOTION              varchar(50) not null,
   STRESS               int not null,
   ANXIETY              int not null,
   SLEEP                int not null,
   NOTES                text not null,
   primary key (EMOTIONAL_DIARY_ID)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*==============================================================*/
/* Table: patient_profile                                       */
/*==============================================================*/
create table patient_profile
(
   USE_USER_ID          bigint not null,
   PATIENT_ID           int not null,
   USER_IMAGE           varchar(250) comment 'meter link de iimagem\r\n',
   FIRST_NAME           varchar(50),
   LAST_NAME            varchar(50),
   E_MAIL               varchar(50),
   PASSWORD             varchar(250),
   ROLE                 enum('Professional','Patient'),
   USER_ID              int not null,
   PATIENT_STATUS       enum('Drop_Out','Followed','Discharged') not null,
   AGE                  int not null,
   START_DATE           date not null,
   TREATMENT_TYPE       enum('Psychological','Medical','Both') not null,
   PROFESSIONAL_CONTACTS int not null,
   primary key (USE_USER_ID, PATIENT_ID)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*==============================================================*/
/* Table: professional_notes                                    */
/*==============================================================*/
create table professional_notes
(
   NOTE_ID              int not null,
   USE_USER_ID          bigint,
   PROFESSIONAL_ID      int,
   USER_ID              int not null,
   NOTE_TITLE           varchar(250) not null,
   NOTE_TEXT            text not null,
   primary key (NOTE_ID)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*==============================================================*/
/* Table: professional_profile                                  */
/*==============================================================*/
create table professional_profile
(
   USE_USER_ID          bigint not null,
   PROFESSIONAL_ID      int not null,
   USER_IMAGE           varchar(250) comment 'meter link de imagem',
   FIRST_NAME           varchar(50),
   LAST_NAME            varchar(50),
   E_MAIL               varchar(50),
   PASSWORD             varchar(250),
   ROLE                 enum('Professional','Patient'),
   USER_ID              int not null,
   PROFESSIONAL_CONTACTS int,
   primary key (USE_USER_ID, PROFESSIONAL_ID)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*==============================================================*/
/* Table: resource_hub                                          */
/*==============================================================*/
create table resource_hub
(
   RESOURCE_ID          int not null,
   USE_USER_ID          bigint,
   PATIENT_ID           int not null,
   PROFESSIONAL_ID      int not null,
   PAT_USE_USER_ID      bigint,
   RESOURCE_PDF         longblob not null,
   primary key (RESOURCE_ID)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*==============================================================*/
/* Table: tic_log                                               */
/*==============================================================*/
create table tic_log
(
   TIC_ID               int not null,
   USE_USER_ID          bigint,
   PATIENT_ID           int not null,
   TYPE_DESCRIPTION     varchar(50) not null,
   MUSCLE_GROUP         varchar(50) not null,
   DURATION             int not null,
   INTENSITY            int not null,
   DESCRIBE_TEXT        text not null,
   SELF_REPORTED        tinyint not null,
   primary key (TIC_ID)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*==============================================================*/
/* Table: track_medication                                        */
/*==============================================================*/
create table track_medication
(
   TRACK_MEDICATION_ID    bigint unsigned not null,
   USE_USER_ID          bigint,
   PATIENT_ID           int,
   USER_ID              int not null,
   MEDICATION_NAME      varchar(50) not null,
   MEDICATION_TIME      datetime not null,
   MEDICATION_STATUS    tinyint not null,
   primary key (TRACK_MEDICINE_ID)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*==============================================================*/
/* Table: user_profile                                          */
/*==============================================================*/
create table user_profile
(
   USER_ID              bigint unsigned not null,
   USER_IMAGE           varchar(250) not null comment 'meter link de iimagem\r\n',
   FIRST_NAME           varchar(50) not null,
   LAST_NAME            varchar(50) not null,
   E_MAIL               varchar(50) not null,
   PASSWORD             varchar(250) not null,
      AGE                  int DEFAULT NULL,
   ROLE                 enum('Professional','Patient') not null,
   primary key (USER_ID)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



/*==============================================================*/
/* Table: password_resets                                      */
/*==============================================================*/


CREATE TABLE password_resets (
  id int(11) NOT NULL AUTO_INCREMENT,
  email varchar(255) NOT NULL,
  token varchar(255) NOT NULL,
  expires bigint(20) NOT NULL,
  PRIMARY KEY (id),
  INDEX (token),
  INDEX (email)
);

/*==============================================================*/
/* Foreign Keys                                                 */
/*==============================================================*/

alter table chat_log add constraint FK_RELATIONSHIP_4 foreign key (USE_USER_ID, PROFESSIONAL_ID)
      references professional_profile (USE_USER_ID, PROFESSIONAL_ID) on delete restrict on update restrict;

alter table chat_log add constraint FK_RELATIONSHIP_5 foreign key (PAT_USE_USER_ID, PATIENT_ID)
      references patient_profile (USE_USER_ID, PATIENT_ID) on delete restrict on update restrict;

alter table emotional_diary add constraint FK_RELATIONSHIP_8 foreign key (USE_USER_ID, PATIENT_ID)
      references patient_profile (USE_USER_ID, PATIENT_ID) on delete restrict on update restrict;

alter table patient_profile add constraint FK_INHERITANCE_2 foreign key (USE_USER_ID)
      references user_profile (USER_ID) on delete restrict on update restrict;

alter table professional_notes add constraint FK_RELATIONSHIP_1 foreign key (USE_USER_ID, PROFESSIONAL_ID)
      references professional_profile (USE_USER_ID, PROFESSIONAL_ID) on delete restrict on update restrict;

alter table professional_profile add constraint FK_INHERITANCE_1 foreign key (USE_USER_ID)
      references user_profile (USER_ID) on delete restrict on update restrict;

alter table resource_hub add constraint FK_RELATIONSHIP_2 foreign key (USE_USER_ID, PROFESSIONAL_ID)
      references professional_profile (USE_USER_ID, PROFESSIONAL_ID) on delete restrict on update restrict;

alter table resource_hub add constraint FK_RELATIONSHIP_3 foreign key (PAT_USE_USER_ID, PATIENT_ID)
      references patient_profile (USE_USER_ID, PATIENT_ID) on delete restrict on update restrict;

alter table tic_log add constraint FK_RELATIONSHIP_6 foreign key (USE_USER_ID, PATIENT_ID)
      references patient_profile (USE_USER_ID, PATIENT_ID) on delete restrict on update restrict;

alter table track_medicine add constraint FK_RELATIONSHIP_7 foreign key (USE_USER_ID, PATIENT_ID)
      references patient_profile (USE_USER_ID, PATIENT_ID) on delete restrict on update restrict;


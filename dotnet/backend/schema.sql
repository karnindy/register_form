IF OBJECT_ID(N'[__EFMigrationsHistory]') IS NULL
BEGIN
    CREATE TABLE [__EFMigrationsHistory] (
        [MigrationId] nvarchar(150) NOT NULL,
        [ProductVersion] nvarchar(32) NOT NULL,
        CONSTRAINT [PK___EFMigrationsHistory] PRIMARY KEY ([MigrationId])
    );
END;
GO

BEGIN TRANSACTION;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [admin_users] (
        [Id] int NOT NULL IDENTITY,
        [Username] nvarchar(50) NOT NULL,
        [PasswordHash] nvarchar(255) NOT NULL,
        [Role] nvarchar(20) NOT NULL,
        [IsActive] bit NOT NULL,
        [CreatedAt] datetime2 NOT NULL,
        CONSTRAINT [PK_admin_users] PRIMARY KEY ([Id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_agent_branches] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        [region_id] int NOT NULL,
        CONSTRAINT [PK_mst_agent_branches] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_agent_regions] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        CONSTRAINT [PK_mst_agent_regions] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_blood] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_blood] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_companies] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_companies] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_districts] (
        [id] int NOT NULL IDENTITY,
        [district_id] int NOT NULL,
        [district_thai] nvarchar(max) NOT NULL,
        [district_english] nvarchar(max) NULL,
        [province_id] int NOT NULL,
        CONSTRAINT [PK_mst_districts] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_expertises] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_expertises] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_gender] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_gender] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_provinces] (
        [id] int NOT NULL IDENTITY,
        [province_id] int NOT NULL,
        [province_thai] nvarchar(max) NOT NULL,
        [province_english] nvarchar(max) NULL,
        CONSTRAINT [PK_mst_provinces] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_religion] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_religion] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_renew_basic] (
        [id] int NOT NULL IDENTITY,
        [course_name] nvarchar(max) NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [date_id] int NULL,
        [agent_type] nvarchar(max) NULL,
        CONSTRAINT [PK_mst_renew_basic] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_renew_course] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_renew_course] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_renew_dates] (
        [id] int NOT NULL IDENTITY,
        [course_date_display] nvarchar(max) NOT NULL,
        [course_date] datetime2 NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_renew_dates] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_renew_other] (
        [id] int NOT NULL IDENTITY,
        [pillar_id] int NOT NULL,
        [date_id] int NOT NULL,
        [subject_id] int NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_renew_other] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_renew_pillars] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_renew_pillars] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_sub_districts] (
        [id] int NOT NULL IDENTITY,
        [sub_district_id] int NOT NULL,
        [sub_district_thai] nvarchar(max) NOT NULL,
        [sub_district_english] nvarchar(max) NULL,
        [district_id] int NOT NULL,
        [postal_code] nvarchar(max) NULL,
        CONSTRAINT [PK_mst_sub_districts] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_territories] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_territories] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [mst_titles] (
        [id] int NOT NULL IDENTITY,
        [name] nvarchar(max) NOT NULL,
        [status] nvarchar(20) NOT NULL,
        [display_order] int NOT NULL,
        CONSTRAINT [PK_mst_titles] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [person] (
        [NationId] nvarchar(13) NOT NULL,
        [IdCardExpiry] datetime2 NULL,
        [TitleTh] nvarchar(max) NULL,
        [FirstNameTh] nvarchar(max) NULL,
        [MiddleNameTh] nvarchar(max) NULL,
        [LastNameTh] nvarchar(max) NULL,
        [TitleOldTh] nvarchar(max) NULL,
        [FirstNameOldTh] nvarchar(max) NULL,
        [MiddleNameOldTh] nvarchar(max) NULL,
        [LastNameOldTh] nvarchar(max) NULL,
        [BirthDate] datetime2 NULL,
        [ReligionId] int NULL,
        [GenderId] int NULL,
        [BloodGroupId] int NULL,
        [PhoneOtp] nvarchar(max) NULL,
        [EmailAlt] nvarchar(max) NULL,
        [LineId] nvarchar(max) NULL,
        [Facebook] nvarchar(max) NULL,
        [Instagram] nvarchar(max) NULL,
        [FoodAllergy] nvarchar(max) NULL,
        [MedicalCondition] nvarchar(max) NULL,
        [EmergencyContactName] nvarchar(max) NULL,
        [EmergencyContactPhone] nvarchar(max) NULL,
        CONSTRAINT [PK_person] PRIMARY KEY ([NationId])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [register] (
        [id] int NOT NULL IDENTITY,
        [pdpa_consent] nvarchar(max) NULL,
        [national_id] nvarchar(max) NULL,
        [id_card_expiry] datetime2 NULL,
        [title_th] nvarchar(max) NULL,
        [title_custom] nvarchar(max) NULL,
        [first_name_th] nvarchar(max) NULL,
        [middle_name_th] nvarchar(max) NULL,
        [last_name_th] nvarchar(max) NULL,
        [first_name_en] nvarchar(max) NULL,
        [middle_name_en] nvarchar(max) NULL,
        [last_name_en] nvarchar(max) NULL,
        [first_name_old_th] nvarchar(max) NULL,
        [middle_name_old_th] nvarchar(max) NULL,
        [last_name_old_th] nvarchar(max) NULL,
        [birth_date] datetime2 NULL,
        [religion] nvarchar(max) NULL,
        [gender] nvarchar(max) NULL,
        [blood_group] nvarchar(max) NULL,
        [phone_otp] nvarchar(max) NULL,
        [email_alt] nvarchar(max) NULL,
        [line_id] nvarchar(max) NULL,
        [facebook] nvarchar(max) NULL,
        [instagram] nvarchar(max) NULL,
        [food_allergy] nvarchar(max) NULL,
        [medical_condition] nvarchar(max) NULL,
        [emergency_contact_name] nvarchar(max) NULL,
        [emergency_contact_phone] nvarchar(max) NULL,
        [addr_house_no] nvarchar(max) NULL,
        [addr_moo] nvarchar(max) NULL,
        [addr_village] nvarchar(max) NULL,
        [addr_soi] nvarchar(max) NULL,
        [addr_road] nvarchar(max) NULL,
        [addr_province] nvarchar(max) NULL,
        [addr_district] nvarchar(max) NULL,
        [addr_subdistrict] nvarchar(max) NULL,
        [addr_postcode] nvarchar(max) NULL,
        [contact_address] nvarchar(max) NULL,
        [contact_house_no] nvarchar(max) NULL,
        [contact_moo] nvarchar(max) NULL,
        [contact_village] nvarchar(max) NULL,
        [contact_soi] nvarchar(max) NULL,
        [contact_road] nvarchar(max) NULL,
        [contact_province] nvarchar(max) NULL,
        [contact_district] nvarchar(max) NULL,
        [contact_subdistrict] nvarchar(max) NULL,
        [contact_postcode] nvarchar(max) NULL,
        [license_type] nvarchar(max) NULL,
        [license_status] nvarchar(max) NULL,
        [license_no] nvarchar(max) NULL,
        [license_issue_date] datetime2 NULL,
        [license_expiry_date] datetime2 NULL,
        [region_affiliation] nvarchar(max) NULL,
        [region_north] nvarchar(max) NULL,
        [region_northeast] nvarchar(max) NULL,
        [region_east] nvarchar(max) NULL,
        [region_central_west] nvarchar(max) NULL,
        [region_south] nvarchar(max) NULL,
        [region_bangkok] nvarchar(max) NULL,
        [broker_company] nvarchar(max) NULL,
        [viriyah_agent_code] nvarchar(max) NULL,
        [course_type] nvarchar(max) NULL,
        [course_type_code] nvarchar(max) NULL,
        [agent_level] nvarchar(max) NULL,
        [broker_level] nvarchar(max) NULL,
        [renew_agent_1] nvarchar(max) NULL,
        [renew_agent_2] nvarchar(max) NULL,
        [renew_agent_3] nvarchar(max) NULL,
        [renew_broker_1] nvarchar(max) NULL,
        [renew_broker_2] nvarchar(max) NULL,
        [renew_broker_3] nvarchar(max) NULL,
        [renew_other] nvarchar(max) NULL,
        [training_exemption] nvarchar(max) NULL,
        [past_training_5y] nvarchar(max) NULL,
        [extra_training_interest] nvarchar(max) NULL,
        [highest_education] nvarchar(max) NULL,
        [main_business] nvarchar(max) NULL,
        [broker_branch] nvarchar(max) NULL,
        [insurance_experience_years] decimal(18,2) NULL,
        [sales_area] nvarchar(max) NULL,
        [other_insurance_companies] nvarchar(max) NULL,
        [insurance_specialty] nvarchar(max) NULL,
        [has_changed_name] nvarchar(max) NULL,
        [title_prev] nvarchar(max) NULL,
        [title_custom_prev] nvarchar(max) NULL,
        [first_name_en_prev] nvarchar(max) NULL,
        [middle_name_en_prev] nvarchar(max) NULL,
        [last_name_en_prev] nvarchar(max) NULL,
        [agent_branch] nvarchar(max) NULL,
        [training_exemption_yet] nvarchar(max) NULL,
        [training_date] nvarchar(max) NULL,
        [training_format] nvarchar(max) NULL,
        [deduction_privilege] nvarchar(max) NULL,
        [previous_courses] nvarchar(max) NULL,
        [additional_course_requirement] nvarchar(max) NULL,
        [has_experience] nvarchar(max) NULL,
        [expectation] nvarchar(max) NULL,
        [certify_true] nvarchar(max) NULL,
        [created_at] datetime2 NULL,
        [updated_at] datetime2 NULL,
        [confirmed] nvarchar(max) NULL,
        [start_time] datetime2 NULL,
        [completion_time] datetime2 NULL,
        [email] nvarchar(max) NULL,
        [form_name] nvarchar(max) NULL,
        [last_modified_time] nvarchar(max) NULL,
        [past_training_5y_course_id] bigint NULL,
        [renew_other_course_id] bigint NULL,
        [remark] nvarchar(max) NULL,
        CONSTRAINT [PK_register] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [register_history] (
        [id] int NOT NULL IDENTITY,
        [register_id] int NOT NULL,
        [edited_by_type] nvarchar(max) NOT NULL,
        [created_by] nvarchar(max) NOT NULL,
        [old_data] nvarchar(max) NULL,
        [new_data] nvarchar(max) NULL,
        [created_at] datetime2 NULL,
        CONSTRAINT [PK_register_history] PRIMARY KEY ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [sys_config] (
        [key_name] nvarchar(100) NOT NULL,
        [value] nvarchar(max) NOT NULL,
        [description] nvarchar(max) NULL,
        [group_name] nvarchar(50) NOT NULL,
        [default_value] nvarchar(max) NULL,
        CONSTRAINT [PK_sys_config] PRIMARY KEY ([key_name])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [personaddress] (
        [Id] int NOT NULL IDENTITY,
        [NationId] nvarchar(13) NOT NULL,
        [HouseNo] nvarchar(max) NULL,
        [Moo] nvarchar(max) NULL,
        [Village] nvarchar(max) NULL,
        [Soi] nvarchar(max) NULL,
        [Road] nvarchar(max) NULL,
        [ProvinceId] int NULL,
        [DistrictId] int NULL,
        [SubDistrictId] int NULL,
        [Postcode] nvarchar(max) NULL,
        [AddressTypeText] nvarchar(max) NULL,
        [AddressType] nvarchar(max) NULL,
        CONSTRAINT [PK_personaddress] PRIMARY KEY ([Id]),
        CONSTRAINT [FK_personaddress_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [personaffiliation] (
        [Id] int NOT NULL IDENTITY,
        [NationId] nvarchar(13) NOT NULL,
        [RegionId] int NULL,
        [BranchId] int NULL,
        [BrokerCompany] nvarchar(max) NULL,
        [BrokerBranch] nvarchar(max) NULL,
        [ViriyahAgentCode] nvarchar(max) NULL,
        [BrokerType] nvarchar(max) NULL,
        CONSTRAINT [PK_personaffiliation] PRIMARY KEY ([Id]),
        CONSTRAINT [FK_personaffiliation_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [personcourse] (
        [PersonCourseId] int NOT NULL IDENTITY,
        [NationId] nvarchar(13) NOT NULL,
        [CourseId] int NULL,
        [CourseDateId] int NULL,
        [RenewOtherId] int NULL,
        CONSTRAINT [PK_personcourse] PRIMARY KEY ([PersonCourseId]),
        CONSTRAINT [FK_personcourse_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [personlicense] (
        [Id] int NOT NULL IDENTITY,
        [NationId] nvarchar(13) NOT NULL,
        [LicenseNo] nvarchar(max) NULL,
        [LicenseIssueDate] datetime2 NULL,
        [LicenseExpiryDate] datetime2 NULL,
        [CourseType] nvarchar(max) NULL,
        [CourseTypeCode] nvarchar(max) NULL,
        CONSTRAINT [PK_personlicense] PRIMARY KEY ([Id]),
        CONSTRAINT [FK_personlicense_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [personother] (
        [id] int NOT NULL IDENTITY,
        [NationId] nvarchar(13) NOT NULL,
        [ExtraTrainingInterest] nvarchar(max) NULL,
        [OtherBusiness] nvarchar(max) NULL,
        [BrokerBranch] nvarchar(max) NULL,
        [InsuranceExperienceYears] int NULL,
        CONSTRAINT [PK_personother] PRIMARY KEY ([id]),
        CONSTRAINT [FK_personother_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [personregistration] (
        [Id] int NOT NULL IDENTITY,
        [NationId] nvarchar(13) NOT NULL,
        [PdpaConsent] bit NULL,
        [start_time] datetime2 NULL,
        [completion_time] datetime2 NULL,
        [confirmed] bit NULL,
        CONSTRAINT [PK_personregistration] PRIMARY KEY ([Id]),
        CONSTRAINT [FK_personregistration_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [persontraining5y] (
        [Id] int NOT NULL IDENTITY,
        [NationId] nvarchar(13) NOT NULL,
        [CourseId] int NULL,
        CONSTRAINT [PK_persontraining5y] PRIMARY KEY ([Id]),
        CONSTRAINT [FK_persontraining5y_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [personothercompanies] (
        [Id] int NOT NULL IDENTITY,
        [OtherId] int NULL,
        [NationId] nvarchar(13) NOT NULL,
        [CompanyId] int NULL,
        CONSTRAINT [PK_personothercompanies] PRIMARY KEY ([Id]),
        CONSTRAINT [FK_personothercompanies_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE,
        CONSTRAINT [FK_personothercompanies_personother_OtherId] FOREIGN KEY ([OtherId]) REFERENCES [personother] ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [personothersalesarea] (
        [Id] int NOT NULL IDENTITY,
        [OtherId] int NULL,
        [NationId] nvarchar(13) NOT NULL,
        [TerritoriesId] int NULL,
        CONSTRAINT [PK_personothersalesarea] PRIMARY KEY ([Id]),
        CONSTRAINT [FK_personothersalesarea_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE,
        CONSTRAINT [FK_personothersalesarea_personother_OtherId] FOREIGN KEY ([OtherId]) REFERENCES [personother] ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [personotherspecialty] (
        [Id] int NOT NULL IDENTITY,
        [OtherId] int NULL,
        [NationId] nvarchar(13) NOT NULL,
        [ExpertiseId] int NULL,
        CONSTRAINT [PK_personotherspecialty] PRIMARY KEY ([Id]),
        CONSTRAINT [FK_personotherspecialty_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE,
        CONSTRAINT [FK_personotherspecialty_personother_OtherId] FOREIGN KEY ([OtherId]) REFERENCES [personother] ([id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE TABLE [registrationhistory] (
        [Id] int NOT NULL IDENTITY,
        [RegisterId] int NULL,
        [NationId] nvarchar(13) NOT NULL,
        [EditedByType] nvarchar(max) NULL,
        [CreatedBy] nvarchar(max) NULL,
        [OldData] nvarchar(max) NULL,
        [NewData] nvarchar(max) NULL,
        [CreatedAt] datetime2 NULL,
        CONSTRAINT [PK_registrationhistory] PRIMARY KEY ([Id]),
        CONSTRAINT [FK_registrationhistory_person_NationId] FOREIGN KEY ([NationId]) REFERENCES [person] ([NationId]) ON DELETE CASCADE,
        CONSTRAINT [FK_registrationhistory_personregistration_RegisterId] FOREIGN KEY ([RegisterId]) REFERENCES [personregistration] ([Id])
    );
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_blood]'))
        SET IDENTITY_INSERT [mst_blood] ON;
    EXEC(N'INSERT INTO [mst_blood] ([id], [display_order], [name], [status])
    VALUES (1, 1, N''A'', N''active''),
    (2, 2, N''B'', N''active''),
    (3, 3, N''AB'', N''active''),
    (4, 4, N''O'', N''active'')');
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_blood]'))
        SET IDENTITY_INSERT [mst_blood] OFF;
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_companies]'))
        SET IDENTITY_INSERT [mst_companies] ON;
    EXEC(N'INSERT INTO [mst_companies] ([id], [display_order], [name], [status])
    VALUES (1, 1, N''บ. กรุงเทพประกันภัย'', N''active''),
    (2, 2, N''บ. กรุงเทพประกันสุขภาพ'', N''active''),
    (3, 3, N''บ. กรุงไทยพานิชประกันภัย'', N''active''),
    (4, 4, N''บ. กลางคุ้มครองผู้ประสบภัยจากรถ'', N''active''),
    (5, 5, N''บ. ชับบ์สามัคคีประกันภัย'', N''active''),
    (6, 6, N''บ. โตเกียวมารีนประกันภัย'', N''active''),
    (7, 7, N''บ. ทิพยประกันภัย'', N''active''),
    (8, 8, N''บ. เทเวศประกันภัย'', N''active''),
    (9, 9, N''บ. ไทยไพบูลย์ประกันภัย'', N''active''),
    (10, 10, N''บ. ไทยวิวัฒน์ประกันภัย'', N''active''),
    (11, 11, N''บ. ไทยศรีประกันภัย'', N''active''),
    (12, 12, N''บ. ไทยเศรษฐกิจประกันภัย'', N''active''),
    (13, 13, N''บ. นวกิจประกันภัย'', N''active''),
    (14, 14, N''บ. บางกอกสหประกันภัย'', N''active''),
    (15, 15, N''บ. ประกันภัยไทยวิวัฒน์'', N''active''),
    (16, 16, N''บ. เมืองไทยประกันภัย'', N''active''),
    (17, 17, N''บ. สินมั่นคงประกันภัย'', N''active''),
    (18, 18, N''บ. อาคเนย์ประกันภัย'', N''active''),
    (19, 19, N''บ. อินทรประกันภัย'', N''active''),
    (20, 20, N''บ. เอเชียประกันภัย 1950'', N''active''),
    (21, 21, N''บ. แอลเอ็มจี ประกันภัย'', N''active''),
    (22, 22, N''บ. เอไอจี ประกันภัย (ประเทศไทย)'', N''active'')');
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_companies]'))
        SET IDENTITY_INSERT [mst_companies] OFF;
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_expertises]'))
        SET IDENTITY_INSERT [mst_expertises] ON;
    EXEC(N'INSERT INTO [mst_expertises] ([id], [display_order], [name], [status])
    VALUES (1, 1, N''ประกันรถยนต์'', N''active''),
    (2, 2, N''ประกันอัคคีภัย/ทรัพย์สิน'', N''active''),
    (3, 3, N''ประกันอุบัติเหตุ'', N''active''),
    (4, 4, N''ประกันสุขภาพ'', N''active''),
    (5, 5, N''ประกันเดินทาง/ทางทะเล'', N''active''),
    (6, 6, N''ประกันการก่อสร้าง/วิศวกรรม'', N''active''),
    (7, 7, N''ประกันภัยความรับผิดของกรรมการ/ผู้บริหาร'', N''active''),
    (8, 8, N''ประกันภัยความรับผิด'', N''active''),
    (9, 9, N''ประกันภัยด้านการเงิน/ค้ำประกัน'', N''active''),
    (10, 10, N''ประกันภัยด้านสิทธิบัตร'', N''active''),
    (11, 11, N''ประกันภัยทางทะเล/ขนส่ง'', N''active''),
    (12, 12, N''ประกันอื่นๆ'', N''active'')');
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_expertises]'))
        SET IDENTITY_INSERT [mst_expertises] OFF;
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_gender]'))
        SET IDENTITY_INSERT [mst_gender] ON;
    EXEC(N'INSERT INTO [mst_gender] ([id], [display_order], [name], [status])
    VALUES (1, 1, N''ชาย'', N''active''),
    (2, 2, N''หญิง'', N''active'')');
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_gender]'))
        SET IDENTITY_INSERT [mst_gender] OFF;
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_religion]'))
        SET IDENTITY_INSERT [mst_religion] ON;
    EXEC(N'INSERT INTO [mst_religion] ([id], [display_order], [name], [status])
    VALUES (1, 1, N''พุทธ'', N''active''),
    (2, 2, N''คริสต์'', N''active''),
    (3, 3, N''อิสลาม'', N''active''),
    (4, 4, N''ซิกข์'', N''active''),
    (5, 5, N''ฮินดู'', N''active''),
    (6, 6, N''อื่นๆ'', N''active'')');
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_religion]'))
        SET IDENTITY_INSERT [mst_religion] OFF;
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_territories]'))
        SET IDENTITY_INSERT [mst_territories] ON;
    EXEC(N'INSERT INTO [mst_territories] ([id], [display_order], [name], [status])
    VALUES (1, 1, N''ภาคกลาง'', N''active''),
    (2, 2, N''ภาคเหนือ'', N''active''),
    (3, 3, N''ภาคตะวันออกเฉียงเหนือ'', N''active''),
    (4, 4, N''ภาคตะวันออก'', N''active''),
    (5, 5, N''ภาคตะวันตก'', N''active''),
    (6, 6, N''ภาคใต้'', N''active'')');
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_territories]'))
        SET IDENTITY_INSERT [mst_territories] OFF;
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_titles]'))
        SET IDENTITY_INSERT [mst_titles] ON;
    EXEC(N'INSERT INTO [mst_titles] ([id], [display_order], [name], [status])
    VALUES (1, 1, N''นาย'', N''active''),
    (2, 2, N''นาง'', N''active''),
    (3, 3, N''นางสาว'', N''active''),
    (4, 4, N''ด.ช.'', N''active''),
    (5, 5, N''ด.ญ.'', N''active''),
    (6, 6, N''ยศ/อื่นๆ (โปรดระบุ)'', N''active'')');
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'id', N'display_order', N'name', N'status') AND [object_id] = OBJECT_ID(N'[mst_titles]'))
        SET IDENTITY_INSERT [mst_titles] OFF;
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'key_name', N'default_value', N'description', N'group_name', N'value') AND [object_id] = OBJECT_ID(N'[sys_config]'))
        SET IDENTITY_INSERT [sys_config] ON;
    EXEC(N'INSERT INTO [sys_config] ([key_name], [default_value], [description], [group_name], [value])
    VALUES (N''APP_ENV'', NULL, N''Environment (dev, uat, prd)'', N''system'', N''prd''),
    (N''SESSION_TIMEOUT'', NULL, N''Session timeout in seconds'', N''system'', N''3600''),
    (N''SYSTEM_IS_ONLINE'', NULL, N''Is system online'', N''system'', N''true''),
    (N''SYSTEM_OPEN_PERIODS'', NULL, N''JSON array of open periods'', N''system'', N''[]''),
    (N''tab4_allowed_agent_types'', NULL, N''Allowed agent types (both, agent, broker)'', N''tab4'', N''both''),
    (N''tab4_default_agentcode'', NULL, N''Default value for Agent Code'', N''tab4'', N''''),
    (N''tab4_default_branch'', NULL, N''Default value for Branch'', N''tab4'', N''''),
    (N''tab4_hint_agentcode'', NULL, N''Hint for Agent Code'', N''tab4'', N''ถ้าไม่ทราบ สอบถามสาขา หรือตัวแทน/นายหน้าที่ท่านสังกัด, ถ้าเป็นขอรับใบอนุญาต และยังไม่มีรหัส ให้กรอก 00000''),
    (N''tab4_hint_branch'', NULL, N''Hint for Branch'', N''tab4'', N''พิมพ์เพื่อค้นหาสาขา''),
    (N''tab4_hint_region'', NULL, N''Hint for Region'', N''tab4'', N''ระบบจะเติมให้อัตโนมัติ''),
    (N''tab4_label_agentcode'', NULL, N''Label for Agent Code'', N''tab4'', N''รหัสที่มีสัญญากับ บมจ.วิริยะประกันภัย *''),
    (N''tab4_label_branch'', NULL, N''Label for Branch'', N''tab4'', N''สาขา *''),
    (N''tab4_label_region'', NULL, N''Label for Region'', N''tab4'', N''ภาค *'')');
    IF EXISTS (SELECT * FROM [sys].[identity_columns] WHERE [name] IN (N'key_name', N'default_value', N'description', N'group_name', N'value') AND [object_id] = OBJECT_ID(N'[sys_config]'))
        SET IDENTITY_INSERT [sys_config] OFF;
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personaddress_NationId] ON [personaddress] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personaffiliation_NationId] ON [personaffiliation] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personcourse_NationId] ON [personcourse] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personlicense_NationId] ON [personlicense] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personother_NationId] ON [personother] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personothercompanies_NationId] ON [personothercompanies] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personothercompanies_OtherId] ON [personothercompanies] ([OtherId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personothersalesarea_NationId] ON [personothersalesarea] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personothersalesarea_OtherId] ON [personothersalesarea] ([OtherId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personotherspecialty_NationId] ON [personotherspecialty] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personotherspecialty_OtherId] ON [personotherspecialty] ([OtherId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_personregistration_NationId] ON [personregistration] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_persontraining5y_NationId] ON [persontraining5y] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_registrationhistory_NationId] ON [registrationhistory] ([NationId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    CREATE INDEX [IX_registrationhistory_RegisterId] ON [registrationhistory] ([RegisterId]);
END;
GO

IF NOT EXISTS (
    SELECT * FROM [__EFMigrationsHistory]
    WHERE [MigrationId] = N'20260722062838_InitialCreateSqlServer'
)
BEGIN
    INSERT INTO [__EFMigrationsHistory] ([MigrationId], [ProductVersion])
    VALUES (N'20260722062838_InitialCreateSqlServer', N'8.0.0');
END;
GO

COMMIT;
GO


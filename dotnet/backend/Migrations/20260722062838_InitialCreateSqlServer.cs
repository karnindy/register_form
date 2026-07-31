using System;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

#pragma warning disable CA1814 // Prefer jagged arrays over multidimensional

namespace backend.Migrations
{
    /// <inheritdoc />
    public partial class InitialCreateSqlServer : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.CreateTable(
                name: "admin_users",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    Username = table.Column<string>(type: "nvarchar(50)", maxLength: 50, nullable: false),
                    PasswordHash = table.Column<string>(type: "nvarchar(255)", maxLength: 255, nullable: false),
                    Role = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    IsActive = table.Column<bool>(type: "bit", nullable: false),
                    CreatedAt = table.Column<DateTime>(type: "datetime2", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_admin_users", x => x.Id);
                });

            migrationBuilder.CreateTable(
                name: "mst_agent_branches",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    region_id = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_agent_branches", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_agent_regions",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_agent_regions", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_blood",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_blood", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_companies",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_companies", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_districts",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    district_id = table.Column<int>(type: "int", nullable: false),
                    district_thai = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    district_english = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    province_id = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_districts", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_expertises",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_expertises", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_gender",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_gender", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_provinces",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    province_id = table.Column<int>(type: "int", nullable: false),
                    province_thai = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    province_english = table.Column<string>(type: "nvarchar(max)", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_provinces", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_religion",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_religion", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_renew_basic",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    course_name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    date_id = table.Column<int>(type: "int", nullable: true),
                    agent_type = table.Column<string>(type: "nvarchar(max)", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_renew_basic", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_renew_course",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_renew_course", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_renew_dates",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    course_date_display = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    course_date = table.Column<DateTime>(type: "datetime2", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_renew_dates", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_renew_other",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    pillar_id = table.Column<int>(type: "int", nullable: false),
                    date_id = table.Column<int>(type: "int", nullable: false),
                    subject_id = table.Column<int>(type: "int", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_renew_other", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_renew_pillars",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_renew_pillars", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_sub_districts",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    sub_district_id = table.Column<int>(type: "int", nullable: false),
                    sub_district_thai = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    sub_district_english = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    district_id = table.Column<int>(type: "int", nullable: false),
                    postal_code = table.Column<string>(type: "nvarchar(max)", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_sub_districts", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_territories",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_territories", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "mst_titles",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    name = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    status = table.Column<string>(type: "nvarchar(20)", maxLength: 20, nullable: false),
                    display_order = table.Column<int>(type: "int", nullable: false)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_mst_titles", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "person",
                columns: table => new
                {
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    IdCardExpiry = table.Column<DateTime>(type: "datetime2", nullable: true),
                    TitleTh = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    FirstNameTh = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    MiddleNameTh = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    LastNameTh = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    TitleOldTh = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    FirstNameOldTh = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    MiddleNameOldTh = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    LastNameOldTh = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    BirthDate = table.Column<DateTime>(type: "datetime2", nullable: true),
                    ReligionId = table.Column<int>(type: "int", nullable: true),
                    GenderId = table.Column<int>(type: "int", nullable: true),
                    BloodGroupId = table.Column<int>(type: "int", nullable: true),
                    PhoneOtp = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    EmailAlt = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    LineId = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    Facebook = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    Instagram = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    FoodAllergy = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    MedicalCondition = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    EmergencyContactName = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    EmergencyContactPhone = table.Column<string>(type: "nvarchar(max)", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_person", x => x.NationId);
                });

            migrationBuilder.CreateTable(
                name: "register",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    pdpa_consent = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    national_id = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    id_card_expiry = table.Column<DateTime>(type: "datetime2", nullable: true),
                    title_th = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    title_custom = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    first_name_th = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    middle_name_th = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    last_name_th = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    first_name_en = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    middle_name_en = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    last_name_en = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    first_name_old_th = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    middle_name_old_th = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    last_name_old_th = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    birth_date = table.Column<DateTime>(type: "datetime2", nullable: true),
                    religion = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    gender = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    blood_group = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    phone_otp = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    email_alt = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    line_id = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    facebook = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    instagram = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    food_allergy = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    medical_condition = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    emergency_contact_name = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    emergency_contact_phone = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    addr_house_no = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    addr_moo = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    addr_village = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    addr_soi = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    addr_road = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    addr_province = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    addr_district = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    addr_subdistrict = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    addr_postcode = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    contact_address = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    contact_house_no = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    contact_moo = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    contact_village = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    contact_soi = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    contact_road = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    contact_province = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    contact_district = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    contact_subdistrict = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    contact_postcode = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    license_type = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    license_status = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    license_no = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    license_issue_date = table.Column<DateTime>(type: "datetime2", nullable: true),
                    license_expiry_date = table.Column<DateTime>(type: "datetime2", nullable: true),
                    region_affiliation = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    region_north = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    region_northeast = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    region_east = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    region_central_west = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    region_south = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    region_bangkok = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    broker_company = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    viriyah_agent_code = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    course_type = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    course_type_code = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    agent_level = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    broker_level = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    renew_agent_1 = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    renew_agent_2 = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    renew_agent_3 = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    renew_broker_1 = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    renew_broker_2 = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    renew_broker_3 = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    renew_other = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    training_exemption = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    past_training_5y = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    extra_training_interest = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    highest_education = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    main_business = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    broker_branch = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    insurance_experience_years = table.Column<decimal>(type: "decimal(18,2)", nullable: true),
                    sales_area = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    other_insurance_companies = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    insurance_specialty = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    has_changed_name = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    title_prev = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    title_custom_prev = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    first_name_en_prev = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    middle_name_en_prev = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    last_name_en_prev = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    agent_branch = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    training_exemption_yet = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    training_date = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    training_format = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    deduction_privilege = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    previous_courses = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    additional_course_requirement = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    has_experience = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    expectation = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    certify_true = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    created_at = table.Column<DateTime>(type: "datetime2", nullable: true),
                    updated_at = table.Column<DateTime>(type: "datetime2", nullable: true),
                    confirmed = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    start_time = table.Column<DateTime>(type: "datetime2", nullable: true),
                    completion_time = table.Column<DateTime>(type: "datetime2", nullable: true),
                    email = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    form_name = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    last_modified_time = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    past_training_5y_course_id = table.Column<long>(type: "bigint", nullable: true),
                    renew_other_course_id = table.Column<long>(type: "bigint", nullable: true),
                    remark = table.Column<string>(type: "nvarchar(max)", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_register", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "register_history",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    register_id = table.Column<int>(type: "int", nullable: false),
                    edited_by_type = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    created_by = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    old_data = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    new_data = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    created_at = table.Column<DateTime>(type: "datetime2", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_register_history", x => x.id);
                });

            migrationBuilder.CreateTable(
                name: "sys_config",
                columns: table => new
                {
                    key_name = table.Column<string>(type: "nvarchar(100)", maxLength: 100, nullable: false),
                    value = table.Column<string>(type: "nvarchar(max)", nullable: false),
                    description = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    group_name = table.Column<string>(type: "nvarchar(50)", maxLength: 50, nullable: false),
                    default_value = table.Column<string>(type: "nvarchar(max)", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_sys_config", x => x.key_name);
                });

            migrationBuilder.CreateTable(
                name: "personaddress",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    HouseNo = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    Moo = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    Village = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    Soi = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    Road = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    ProvinceId = table.Column<int>(type: "int", nullable: true),
                    DistrictId = table.Column<int>(type: "int", nullable: true),
                    SubDistrictId = table.Column<int>(type: "int", nullable: true),
                    Postcode = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    AddressTypeText = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    AddressType = table.Column<string>(type: "nvarchar(max)", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_personaddress", x => x.Id);
                    table.ForeignKey(
                        name: "FK_personaddress_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateTable(
                name: "personaffiliation",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    RegionId = table.Column<int>(type: "int", nullable: true),
                    BranchId = table.Column<int>(type: "int", nullable: true),
                    BrokerCompany = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    BrokerBranch = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    ViriyahAgentCode = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    BrokerType = table.Column<string>(type: "nvarchar(max)", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_personaffiliation", x => x.Id);
                    table.ForeignKey(
                        name: "FK_personaffiliation_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateTable(
                name: "personcourse",
                columns: table => new
                {
                    PersonCourseId = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    CourseId = table.Column<int>(type: "int", nullable: true),
                    CourseDateId = table.Column<int>(type: "int", nullable: true),
                    RenewOtherId = table.Column<int>(type: "int", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_personcourse", x => x.PersonCourseId);
                    table.ForeignKey(
                        name: "FK_personcourse_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateTable(
                name: "personlicense",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    LicenseNo = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    LicenseIssueDate = table.Column<DateTime>(type: "datetime2", nullable: true),
                    LicenseExpiryDate = table.Column<DateTime>(type: "datetime2", nullable: true),
                    CourseType = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    CourseTypeCode = table.Column<string>(type: "nvarchar(max)", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_personlicense", x => x.Id);
                    table.ForeignKey(
                        name: "FK_personlicense_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateTable(
                name: "personother",
                columns: table => new
                {
                    id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    ExtraTrainingInterest = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    OtherBusiness = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    BrokerBranch = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    InsuranceExperienceYears = table.Column<int>(type: "int", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_personother", x => x.id);
                    table.ForeignKey(
                        name: "FK_personother_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateTable(
                name: "personregistration",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    PdpaConsent = table.Column<bool>(type: "bit", nullable: true),
                    start_time = table.Column<DateTime>(type: "datetime2", nullable: true),
                    completion_time = table.Column<DateTime>(type: "datetime2", nullable: true),
                    confirmed = table.Column<bool>(type: "bit", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_personregistration", x => x.Id);
                    table.ForeignKey(
                        name: "FK_personregistration_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateTable(
                name: "persontraining5y",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    CourseId = table.Column<int>(type: "int", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_persontraining5y", x => x.Id);
                    table.ForeignKey(
                        name: "FK_persontraining5y_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateTable(
                name: "personothercompanies",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    OtherId = table.Column<int>(type: "int", nullable: true),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    CompanyId = table.Column<int>(type: "int", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_personothercompanies", x => x.Id);
                    table.ForeignKey(
                        name: "FK_personothercompanies_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                    table.ForeignKey(
                        name: "FK_personothercompanies_personother_OtherId",
                        column: x => x.OtherId,
                        principalTable: "personother",
                        principalColumn: "id");
                });

            migrationBuilder.CreateTable(
                name: "personothersalesarea",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    OtherId = table.Column<int>(type: "int", nullable: true),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    TerritoriesId = table.Column<int>(type: "int", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_personothersalesarea", x => x.Id);
                    table.ForeignKey(
                        name: "FK_personothersalesarea_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                    table.ForeignKey(
                        name: "FK_personothersalesarea_personother_OtherId",
                        column: x => x.OtherId,
                        principalTable: "personother",
                        principalColumn: "id");
                });

            migrationBuilder.CreateTable(
                name: "personotherspecialty",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    OtherId = table.Column<int>(type: "int", nullable: true),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    ExpertiseId = table.Column<int>(type: "int", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_personotherspecialty", x => x.Id);
                    table.ForeignKey(
                        name: "FK_personotherspecialty_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                    table.ForeignKey(
                        name: "FK_personotherspecialty_personother_OtherId",
                        column: x => x.OtherId,
                        principalTable: "personother",
                        principalColumn: "id");
                });

            migrationBuilder.CreateTable(
                name: "registrationhistory",
                columns: table => new
                {
                    Id = table.Column<int>(type: "int", nullable: false)
                        .Annotation("SqlServer:Identity", "1, 1"),
                    RegisterId = table.Column<int>(type: "int", nullable: true),
                    NationId = table.Column<string>(type: "nvarchar(13)", maxLength: 13, nullable: false),
                    EditedByType = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    CreatedBy = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    OldData = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    NewData = table.Column<string>(type: "nvarchar(max)", nullable: true),
                    CreatedAt = table.Column<DateTime>(type: "datetime2", nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_registrationhistory", x => x.Id);
                    table.ForeignKey(
                        name: "FK_registrationhistory_person_NationId",
                        column: x => x.NationId,
                        principalTable: "person",
                        principalColumn: "NationId",
                        onDelete: ReferentialAction.Cascade);
                    table.ForeignKey(
                        name: "FK_registrationhistory_personregistration_RegisterId",
                        column: x => x.RegisterId,
                        principalTable: "personregistration",
                        principalColumn: "Id");
                });

            migrationBuilder.InsertData(
                table: "mst_blood",
                columns: new[] { "id", "display_order", "name", "status" },
                values: new object[,]
                {
                    { 1, 1, "A", "active" },
                    { 2, 2, "B", "active" },
                    { 3, 3, "AB", "active" },
                    { 4, 4, "O", "active" }
                });

            migrationBuilder.InsertData(
                table: "mst_companies",
                columns: new[] { "id", "display_order", "name", "status" },
                values: new object[,]
                {
                    { 1, 1, "บ. กรุงเทพประกันภัย", "active" },
                    { 2, 2, "บ. กรุงเทพประกันสุขภาพ", "active" },
                    { 3, 3, "บ. กรุงไทยพานิชประกันภัย", "active" },
                    { 4, 4, "บ. กลางคุ้มครองผู้ประสบภัยจากรถ", "active" },
                    { 5, 5, "บ. ชับบ์สามัคคีประกันภัย", "active" },
                    { 6, 6, "บ. โตเกียวมารีนประกันภัย", "active" },
                    { 7, 7, "บ. ทิพยประกันภัย", "active" },
                    { 8, 8, "บ. เทเวศประกันภัย", "active" },
                    { 9, 9, "บ. ไทยไพบูลย์ประกันภัย", "active" },
                    { 10, 10, "บ. ไทยวิวัฒน์ประกันภัย", "active" },
                    { 11, 11, "บ. ไทยศรีประกันภัย", "active" },
                    { 12, 12, "บ. ไทยเศรษฐกิจประกันภัย", "active" },
                    { 13, 13, "บ. นวกิจประกันภัย", "active" },
                    { 14, 14, "บ. บางกอกสหประกันภัย", "active" },
                    { 15, 15, "บ. ประกันภัยไทยวิวัฒน์", "active" },
                    { 16, 16, "บ. เมืองไทยประกันภัย", "active" },
                    { 17, 17, "บ. สินมั่นคงประกันภัย", "active" },
                    { 18, 18, "บ. อาคเนย์ประกันภัย", "active" },
                    { 19, 19, "บ. อินทรประกันภัย", "active" },
                    { 20, 20, "บ. เอเชียประกันภัย 1950", "active" },
                    { 21, 21, "บ. แอลเอ็มจี ประกันภัย", "active" },
                    { 22, 22, "บ. เอไอจี ประกันภัย (ประเทศไทย)", "active" }
                });

            migrationBuilder.InsertData(
                table: "mst_expertises",
                columns: new[] { "id", "display_order", "name", "status" },
                values: new object[,]
                {
                    { 1, 1, "ประกันรถยนต์", "active" },
                    { 2, 2, "ประกันอัคคีภัย/ทรัพย์สิน", "active" },
                    { 3, 3, "ประกันอุบัติเหตุ", "active" },
                    { 4, 4, "ประกันสุขภาพ", "active" },
                    { 5, 5, "ประกันเดินทาง/ทางทะเล", "active" },
                    { 6, 6, "ประกันการก่อสร้าง/วิศวกรรม", "active" },
                    { 7, 7, "ประกันภัยความรับผิดของกรรมการ/ผู้บริหาร", "active" },
                    { 8, 8, "ประกันภัยความรับผิด", "active" },
                    { 9, 9, "ประกันภัยด้านการเงิน/ค้ำประกัน", "active" },
                    { 10, 10, "ประกันภัยด้านสิทธิบัตร", "active" },
                    { 11, 11, "ประกันภัยทางทะเล/ขนส่ง", "active" },
                    { 12, 12, "ประกันอื่นๆ", "active" }
                });

            migrationBuilder.InsertData(
                table: "mst_gender",
                columns: new[] { "id", "display_order", "name", "status" },
                values: new object[,]
                {
                    { 1, 1, "ชาย", "active" },
                    { 2, 2, "หญิง", "active" }
                });

            migrationBuilder.InsertData(
                table: "mst_religion",
                columns: new[] { "id", "display_order", "name", "status" },
                values: new object[,]
                {
                    { 1, 1, "พุทธ", "active" },
                    { 2, 2, "คริสต์", "active" },
                    { 3, 3, "อิสลาม", "active" },
                    { 4, 4, "ซิกข์", "active" },
                    { 5, 5, "ฮินดู", "active" },
                    { 6, 6, "อื่นๆ", "active" }
                });

            migrationBuilder.InsertData(
                table: "mst_territories",
                columns: new[] { "id", "display_order", "name", "status" },
                values: new object[,]
                {
                    { 1, 1, "ภาคกลาง", "active" },
                    { 2, 2, "ภาคเหนือ", "active" },
                    { 3, 3, "ภาคตะวันออกเฉียงเหนือ", "active" },
                    { 4, 4, "ภาคตะวันออก", "active" },
                    { 5, 5, "ภาคตะวันตก", "active" },
                    { 6, 6, "ภาคใต้", "active" }
                });

            migrationBuilder.InsertData(
                table: "mst_titles",
                columns: new[] { "id", "display_order", "name", "status" },
                values: new object[,]
                {
                    { 1, 1, "นาย", "active" },
                    { 2, 2, "นาง", "active" },
                    { 3, 3, "นางสาว", "active" },
                    { 4, 4, "ด.ช.", "active" },
                    { 5, 5, "ด.ญ.", "active" },
                    { 6, 6, "ยศ/อื่นๆ (โปรดระบุ)", "active" }
                });

            migrationBuilder.InsertData(
                table: "sys_config",
                columns: new[] { "key_name", "default_value", "description", "group_name", "value" },
                values: new object[,]
                {
                    { "APP_ENV", null, "Environment (dev, uat, prd)", "system", "prd" },
                    { "SESSION_TIMEOUT", null, "Session timeout in seconds", "system", "3600" },
                    { "SYSTEM_IS_ONLINE", null, "Is system online", "system", "true" },
                    { "SYSTEM_OPEN_PERIODS", null, "JSON array of open periods", "system", "[]" },
                    { "tab4_allowed_agent_types", null, "Allowed agent types (both, agent, broker)", "tab4", "both" },
                    { "tab4_default_agentcode", null, "Default value for Agent Code", "tab4", "" },
                    { "tab4_default_branch", null, "Default value for Branch", "tab4", "" },
                    { "tab4_hint_agentcode", null, "Hint for Agent Code", "tab4", "ถ้าไม่ทราบ สอบถามสาขา หรือตัวแทน/นายหน้าที่ท่านสังกัด, ถ้าเป็นขอรับใบอนุญาต และยังไม่มีรหัส ให้กรอก 00000" },
                    { "tab4_hint_branch", null, "Hint for Branch", "tab4", "พิมพ์เพื่อค้นหาสาขา" },
                    { "tab4_hint_region", null, "Hint for Region", "tab4", "ระบบจะเติมให้อัตโนมัติ" },
                    { "tab4_label_agentcode", null, "Label for Agent Code", "tab4", "รหัสที่มีสัญญากับ บมจ.วิริยะประกันภัย *" },
                    { "tab4_label_branch", null, "Label for Branch", "tab4", "สาขา *" },
                    { "tab4_label_region", null, "Label for Region", "tab4", "ภาค *" }
                });

            migrationBuilder.CreateIndex(
                name: "IX_personaddress_NationId",
                table: "personaddress",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_personaffiliation_NationId",
                table: "personaffiliation",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_personcourse_NationId",
                table: "personcourse",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_personlicense_NationId",
                table: "personlicense",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_personother_NationId",
                table: "personother",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_personothercompanies_NationId",
                table: "personothercompanies",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_personothercompanies_OtherId",
                table: "personothercompanies",
                column: "OtherId");

            migrationBuilder.CreateIndex(
                name: "IX_personothersalesarea_NationId",
                table: "personothersalesarea",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_personothersalesarea_OtherId",
                table: "personothersalesarea",
                column: "OtherId");

            migrationBuilder.CreateIndex(
                name: "IX_personotherspecialty_NationId",
                table: "personotherspecialty",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_personotherspecialty_OtherId",
                table: "personotherspecialty",
                column: "OtherId");

            migrationBuilder.CreateIndex(
                name: "IX_personregistration_NationId",
                table: "personregistration",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_persontraining5y_NationId",
                table: "persontraining5y",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_registrationhistory_NationId",
                table: "registrationhistory",
                column: "NationId");

            migrationBuilder.CreateIndex(
                name: "IX_registrationhistory_RegisterId",
                table: "registrationhistory",
                column: "RegisterId");
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropTable(
                name: "admin_users");

            migrationBuilder.DropTable(
                name: "mst_agent_branches");

            migrationBuilder.DropTable(
                name: "mst_agent_regions");

            migrationBuilder.DropTable(
                name: "mst_blood");

            migrationBuilder.DropTable(
                name: "mst_companies");

            migrationBuilder.DropTable(
                name: "mst_districts");

            migrationBuilder.DropTable(
                name: "mst_expertises");

            migrationBuilder.DropTable(
                name: "mst_gender");

            migrationBuilder.DropTable(
                name: "mst_provinces");

            migrationBuilder.DropTable(
                name: "mst_religion");

            migrationBuilder.DropTable(
                name: "mst_renew_basic");

            migrationBuilder.DropTable(
                name: "mst_renew_course");

            migrationBuilder.DropTable(
                name: "mst_renew_dates");

            migrationBuilder.DropTable(
                name: "mst_renew_other");

            migrationBuilder.DropTable(
                name: "mst_renew_pillars");

            migrationBuilder.DropTable(
                name: "mst_sub_districts");

            migrationBuilder.DropTable(
                name: "mst_territories");

            migrationBuilder.DropTable(
                name: "mst_titles");

            migrationBuilder.DropTable(
                name: "personaddress");

            migrationBuilder.DropTable(
                name: "personaffiliation");

            migrationBuilder.DropTable(
                name: "personcourse");

            migrationBuilder.DropTable(
                name: "personlicense");

            migrationBuilder.DropTable(
                name: "personothercompanies");

            migrationBuilder.DropTable(
                name: "personothersalesarea");

            migrationBuilder.DropTable(
                name: "personotherspecialty");

            migrationBuilder.DropTable(
                name: "persontraining5y");

            migrationBuilder.DropTable(
                name: "register");

            migrationBuilder.DropTable(
                name: "register_history");

            migrationBuilder.DropTable(
                name: "registrationhistory");

            migrationBuilder.DropTable(
                name: "sys_config");

            migrationBuilder.DropTable(
                name: "personother");

            migrationBuilder.DropTable(
                name: "personregistration");

            migrationBuilder.DropTable(
                name: "person");
        }
    }
}

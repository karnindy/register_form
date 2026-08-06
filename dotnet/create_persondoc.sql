CREATE TABLE persondocument (
    Id INT IDENTITY(1,1) PRIMARY KEY,
    NationId NVARCHAR(13),
    DocumentType NVARCHAR(MAX),
    FilePath NVARCHAR(MAX),
    UploadedAt DATETIME2,
    CONSTRAINT FK_persondocument_person_NationId FOREIGN KEY (NationId) REFERENCES person(NationId) ON DELETE CASCADE
);

-- Contoh pembuatan akun & profil (gunakan RETURNING untuk menghindari asumsi ID)
WITH new_account AS (
  INSERT INTO Account (Email, Password, Status)
  VALUES ('sitikusmini@cantabu.app', 'pbkdf2$hash_siti', 'Active')
  RETURNING AccountID
), new_profile AS (
  INSERT INTO Profile (AccountID, DisplayName, Gender, Birthdate, LocationAt, LocationOn, PrefGender, PrefAgeMin, PrefAgeMax)
  SELECT AccountID, 'Siti Kusmini', 'P', '2004-05-06', 'Bandung', 'ID', 'L', 20, 30
  FROM new_account
  RETURNING ProfileID
), ins_photo AS (
  INSERT INTO Photo (ProfileID, StorageUrl, IsPrimary, VerifiedByFaceID, hash)
  SELECT ProfileID, 'https://cdn.cantabu.app/p6.jpg', true, true, 'sha256:f6g7h8i9'
  FROM new_profile
  RETURNING PhotoID, ProfileID
), ins_faceid AS (
  INSERT INTO FaceIDProfile (ProfileID, TemplateEnc, Version)
  SELECT ProfileID, 'BASE64ENC_TEMPLATE_6', '1.0'
  FROM new_profile
  RETURNING FaceProfileID, ProfileID
), ins_ver AS (
  INSERT INTO FaceIDVerification (ProfileID, Result, Confidence, LivenessScore, DeviceInfo, IpAddress)
  SELECT ProfileID, 'Success', 89, 95, 'Android 13; Redmi 14', '192.168.10.14'
  FROM new_profile
  RETURNING VerificationID, ProfileID
)
SELECT np.ProfileID AS created_profile_id, ip.PhotoID, ifa.FaceProfileID, iv.VerificationID
FROM new_profile np
LEFT JOIN ins_photo ip ON ip.ProfileID = np.ProfileID
LEFT JOIN ins_faceid ifa ON ifa.ProfileID = np.ProfileID
LEFT JOIN ins_ver iv ON iv.ProfileID = np.ProfileID;

-- Contoh membuat match (jika ProfileID diketahui dari context)
INSERT INTO Matches (ProfileA_ID, ProfileB_ID, Status)
VALUES (6, 2, 'Unmatched');

-- Contoh pesan dan laporan (gunakan ID nyata/RETURNING pada alur aplikasi)
INSERT INTO Interaction (MatchID, Chat, InteractionType)
VALUES (6, 'Hai! Kenalan yuks', 'Text');

INSERT INTO Report (ReporterProfileID, ReportedProfileID, MatchID, Reason, Details, Status)
VALUES (6, 2, 6, 'Spam', 'Mengirim pesan yang sama berulang', 'UnderReview');

-- Memperbarui data pengguna (misal password)
UPDATE Account
SET Password = 'pbkdf2$hash_kusmini123'
WHERE AccountID = 6;

-- Memperbarui data profil pengguna (misal PrefAgeMin dan PrefAgeMax)
UPDATE Profile
SET PrefAgeMin = 23, PrefAgeMax = 35
WHERE AccountID = 6;

-- Memperbarui data foto Profil
UPDATE Photo
SET StorageUrl = 'https://cdn.cantabu.app/p6.1.jpg',
    IsPrimary = true,
    VerifiedByFaceID = true,
    hash = 'sha256:f6g7h8i91'
WHERE ProfileID = 6;

-- Memperbarui data match
UPDATE Matches
SET Status = 'Active'
WHERE MatchID = 6;

-- Memperbarui data pesan
UPDATE Interaction
SET Chat = 'Makanan kesukaan kamu apa? :v'
WHERE InteractionID = 6;

-- Memperbarui data laporan pengguna
UPDATE Report
SET Status = 'Rejected'
WHERE ReportID = 6;

-- Menghapus data (contoh)
DELETE FROM Report WHERE ReportID = 6;
DELETE FROM Interaction WHERE InteractionID = 6;
DELETE FROM Matches WHERE MatchID = 6;
DELETE FROM Photo WHERE ProfileID = 6;
DELETE FROM FaceIDProfile WHERE ProfileID = 6;
DELETE FROM FaceIDVerification WHERE ProfileID = 6;
DELETE FROM Profile WHERE ProfileID = 6;
DELETE FROM Account WHERE AccountID = 6;

-- Contoh pencarian sederhana 
SELECT P.DisplayName, A.* FROM Account A
JOIN Profile P ON A.AccountID = P.AccountID
WHERE Email LIKE 'wafi%';

SELECT * FROM Profile WHERE DisplayName LIKE 'Al%';

SELECT p.DisplayName, ph.* FROM Photo ph
JOIN Profile p ON ph.ProfileID = p.ProfileID
WHERE ph.VerifiedByFaceID = true;

SELECT PA.DisplayName as ProfileAName, PB.DisplayName as ProfileBName, M.* FROM Matches M
JOIN Profile PA ON M.ProfileA_ID = PA.ProfileID
JOIN Profile PB ON M.ProfileB_ID = PB.ProfileID
WHERE M.Status = 'Active';

SELECT PA.DisplayName as ProfileAName, PB.DisplayName as ProfileBName, I.* FROM Interaction I
JOIN Matches M ON M.MatchID = I.MatchID
JOIN Profile PA ON M.ProfileA_ID = PA.ProfileID
JOIN Profile PB ON M.ProfileB_ID = PB.ProfileID
WHERE I.Chat LIKE '%ngopi%';

SELECT PA.DisplayName as ProfileAName, PB.DisplayName as ProfileBName, I.* FROM Interaction I
JOIN Matches M ON M.MatchID = I.MatchID
JOIN Profile PA ON M.ProfileA_ID = PA.ProfileID
JOIN Profile PB ON M.ProfileB_ID = PB.ProfileID
WHERE I.InteractionID = 2;

SELECT Pr.DisplayName as Reporter, Pd.DisplayName as Reported, R.* FROM Report R
JOIN Profile Pr ON R.ReporterProfileID = Pr.ProfileID
JOIN Profile Pd ON R.ReportedProfileID = Pd.ProfileID
WHERE R.Status = 'UnderReview';

SELECT p.ProfileID, p.DisplayName, ph.IsPrimary, ph.VerifiedByFaceID, fv.Result AS LastResult, fv.Confidence, fv.LivenessScore, fv.CreatedAt AS VerifiedAt
FROM Profile p
LEFT JOIN Photo ph ON p.ProfileID = ph.ProfileID AND ph.IsPrimary = true
LEFT JOIN FaceIDVerification fv ON p.ProfileID = fv.ProfileID
ORDER BY fv.CreatedAt DESC;

SELECT m.MatchID, m.Status, m.MatchedAt, PA.DisplayName AS ProfileA, PB.DisplayName AS ProfileB
FROM Matches m
JOIN Profile PA ON m.ProfileA_ID = PA.ProfileID
JOIN Profile PB ON m.ProfileB_ID = PB.ProfileID;

SELECT I.InteractionID, m.MatchID, PA.DisplayName AS ProfileA, PB.DisplayName AS ProfileB, I.Chat, I.InteractionType, I.CreatedAt
FROM Interaction I
JOIN Matches m ON I.MatchID = m.MatchID
JOIN Profile PA ON m.ProfileA_ID = PA.ProfileID
JOIN Profile PB ON m.ProfileB_ID = PB.ProfileID
ORDER BY I.CreatedAt;

SELECT r.ReportID, r.Status, r.Reason, r.Details, r.CreatedAt, Pr.DisplayName AS Reporter, Pd.DisplayName AS Reported, m.MatchID
FROM Report r
JOIN Profile Pr ON r.ReporterProfileID = Pr.ProfileID
JOIN Profile Pd ON r.ReportedProfileID = Pd.ProfileID
JOIN Matches m ON r.MatchID = m.MatchID
ORDER BY r.CreatedAt desc;

-- Laporan contoh
SELECT DATE_TRUNC('month', createdat) AS bulan, COUNT(*) AS total_pengguna_baru
FROM Account
GROUP BY bulan ORDER BY bulan;

SELECT Status, COUNT(*) AS jumlah
FROM Account
GROUP BY Status ORDER BY jumlah desc;

SELECT Gender, COUNT(*) AS jumlah
FROM Profile
GROUP BY Gender ORDER BY jumlah desc;

SELECT LocationAt, COUNT(*) AS jumlah
FROM Profile
GROUP BY LocationAt ORDER BY jumlah desc;

SELECT DATE_TRUNC('month', createdat) AS bulan, COUNT(*) AS total_foto_terverifikasi
FROM Photo
GROUP BY bulan ORDER BY bulan;

SELECT DATE_TRUNC('month', matchedat) AS bulan, COUNT(*) AS total_matches
FROM Matches WHERE Status = 'Active'
GROUP BY bulan ORDER BY bulan;

SELECT DATE_TRUNC('month', createdat) AS bulan, COUNT(*) AS total_pesan
FROM Interaction WHERE InteractionType = 'Text'
GROUP BY bulan ORDER BY bulan;

SELECT Pd.DisplayName AS Reported, COUNT(*) AS Total_Laporan
FROM Report r
JOIN Profile Pd ON r.ReportedProfileID = Pd.ProfileID
GROUP BY Pd.DisplayName ORDER BY Total_Laporan desc;



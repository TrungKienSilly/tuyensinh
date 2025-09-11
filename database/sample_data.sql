-- Dữ liệu mẫu cho hệ thống quản lý trường đại học
USE university_management;

-- Xóa dữ liệu cũ (nếu có)
DELETE FROM admission_scores;
DELETE FROM majors;
DELETE FROM universities;

-- Thêm dữ liệu trường đại học
INSERT INTO universities (name, code, province, address, website, phone, email, description, established_year, university_type) VALUES
('Đại học Bách khoa Hà Nội', 'BKA', 'Hà Nội', 'Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội', 'https://www.hust.edu.vn', '024 3869 4242', 'info@hust.edu.vn', 'Trường Đại học Bách khoa Hà Nội là một trong những trường đại học kỹ thuật hàng đầu Việt Nam, được thành lập năm 1956. Trường có uy tín cao trong đào tạo kỹ thuật, công nghệ và quản lý.', 1956, 'Công lập'),

('Đại học Kinh tế Quốc dân', 'NEU', 'Hà Nội', '207 Giải Phóng, Hai Bà Trưng, Hà Nội', 'https://www.neu.edu.vn', '024 3628 0280', 'info@neu.edu.vn', 'Trường Đại học Kinh tế Quốc dân là trường đại học kinh tế hàng đầu Việt Nam, được thành lập năm 1956. Trường có thế mạnh trong đào tạo kinh tế, quản trị kinh doanh và các ngành liên quan.', 1956, 'Công lập'),

('Đại học Ngoại thương', 'FTU', 'Hà Nội', '91 Chùa Láng, Đống Đa, Hà Nội', 'https://www.ftu.edu.vn', '024 3835 6800', 'info@ftu.edu.vn', 'Trường Đại học Ngoại thương là trường đại học chuyên đào tạo về kinh tế đối ngoại, thương mại quốc tế và các ngành liên quan. Trường có uy tín cao trong đào tạo nguồn nhân lực cho lĩnh vực kinh tế đối ngoại.', 1960, 'Công lập'),

('Đại học Y Hà Nội', 'HUS', 'Hà Nội', 'Số 1 Tôn Thất Tùng, Đống Đa, Hà Nội', 'https://www.hmu.edu.vn', '024 3852 3859', 'info@hmu.edu.vn', 'Trường Đại học Y Hà Nội là trường đại học y khoa hàng đầu Việt Nam, được thành lập năm 1902. Trường có uy tín cao trong đào tạo bác sĩ và cán bộ y tế.', 1902, 'Công lập'),

('Đại học Sư phạm Hà Nội', 'HNU', 'Hà Nội', '136 Xuân Thủy, Cầu Giấy, Hà Nội', 'https://www.hnue.edu.vn', '024 3754 7851', 'info@hnue.edu.vn', 'Trường Đại học Sư phạm Hà Nội là trường đại học sư phạm hàng đầu Việt Nam, được thành lập năm 1951. Trường có thế mạnh trong đào tạo giáo viên và nghiên cứu giáo dục.', 1951, 'Công lập'),

('Đại học Bách khoa TP.HCM', 'HCMUT', 'TP. Hồ Chí Minh', '268 Lý Thường Kiệt, Quận 10, TP.HCM', 'https://www.hcmut.edu.vn', '028 3865 2017', 'info@hcmut.edu.vn', 'Trường Đại học Bách khoa TP.HCM là trường đại học kỹ thuật hàng đầu phía Nam, được thành lập năm 1957. Trường có uy tín cao trong đào tạo kỹ thuật, công nghệ và quản lý.', 1957, 'Công lập'),

('Đại học Kinh tế TP.HCM', 'UEH', 'TP. Hồ Chí Minh', '59C Nguyễn Đình Chiểu, Quận 3, TP.HCM', 'https://www.ueh.edu.vn', '028 3829 5100', 'info@ueh.edu.vn', 'Trường Đại học Kinh tế TP.HCM là trường đại học kinh tế hàng đầu phía Nam, được thành lập năm 1976. Trường có thế mạnh trong đào tạo kinh tế, quản trị kinh doanh và các ngành liên quan.', 1976, 'Công lập'),

('Đại học Y Dược TP.HCM', 'UMP', 'TP. Hồ Chí Minh', '217 Hồng Bàng, Quận 5, TP.HCM', 'https://www.ump.edu.vn', '028 3855 4269', 'info@ump.edu.vn', 'Trường Đại học Y Dược TP.HCM là trường đại học y khoa hàng đầu phía Nam, được thành lập năm 1947. Trường có uy tín cao trong đào tạo bác sĩ, dược sĩ và cán bộ y tế.', 1947, 'Công lập'),

('Đại học Cần Thơ', 'CTU', 'Cần Thơ', 'Khu II, Đường 3/2, Ninh Kiều, Cần Thơ', 'https://www.ctu.edu.vn', '0292 3832 663', 'info@ctu.edu.vn', 'Trường Đại học Cần Thơ là trường đại học đa ngành hàng đầu Đồng bằng sông Cửu Long, được thành lập năm 1966. Trường có thế mạnh trong đào tạo nông nghiệp, thủy sản và các ngành kỹ thuật.', 1966, 'Công lập'),

('Đại học Đà Nẵng', 'UDA', 'Đà Nẵng', '41 Lê Duẩn, Hải Châu, Đà Nẵng', 'https://www.udn.vn', '0236 3842 041', 'info@udn.vn', 'Trường Đại học Đà Nẵng là trường đại học đa ngành hàng đầu miền Trung, được thành lập năm 1994. Trường có thế mạnh trong đào tạo kỹ thuật, kinh tế và các ngành xã hội.', 1994, 'Công lập');

-- Thêm dữ liệu ngành đào tạo
INSERT INTO majors (university_id, code, name, description, training_level, duration_years) VALUES
-- Đại học Bách khoa Hà Nội
(1, 'IT01', 'Công nghệ thông tin', 'Ngành đào tạo chuyên sâu về lập trình, phát triển phần mềm, mạng máy tính và các công nghệ thông tin hiện đại.', 'Đại học', 4),
(1, 'EE01', 'Điện tử viễn thông', 'Ngành đào tạo về điện tử, viễn thông, hệ thống nhúng và các công nghệ truyền thông.', 'Đại học', 4),
(1, 'ME01', 'Cơ khí', 'Ngành đào tạo về thiết kế, chế tạo và vận hành các hệ thống cơ khí, máy móc.', 'Đại học', 4),
(1, 'CE01', 'Xây dựng', 'Ngành đào tạo về thiết kế, thi công và quản lý các công trình xây dựng.', 'Đại học', 4),

-- Đại học Kinh tế Quốc dân
(2, 'BA01', 'Quản trị kinh doanh', 'Ngành đào tạo về quản lý doanh nghiệp, chiến lược kinh doanh và phát triển tổ chức.', 'Đại học', 4),
(2, 'FI01', 'Tài chính ngân hàng', 'Ngành đào tạo về tài chính, ngân hàng, đầu tư và quản lý rủi ro tài chính.', 'Đại học', 4),
(2, 'AC01', 'Kế toán', 'Ngành đào tạo về kế toán, kiểm toán và quản lý tài chính doanh nghiệp.', 'Đại học', 4),
(2, 'MK01', 'Marketing', 'Ngành đào tạo về marketing, quảng cáo và phát triển thương hiệu.', 'Đại học', 4),

-- Đại học Ngoại thương
(3, 'IB01', 'Kinh tế đối ngoại', 'Ngành đào tạo về kinh tế quốc tế, thương mại quốc tế và hội nhập kinh tế.', 'Đại học', 4),
(3, 'TM01', 'Thương mại quốc tế', 'Ngành đào tạo về xuất nhập khẩu, logistics và quản lý chuỗi cung ứng quốc tế.', 'Đại học', 4),
(3, 'QT01', 'Quản trị kinh doanh quốc tế', 'Ngành đào tạo về quản lý doanh nghiệp trong môi trường quốc tế.', 'Đại học', 4),

-- Đại học Y Hà Nội
(4, 'YD01', 'Y đa khoa', 'Ngành đào tạo bác sĩ đa khoa, chẩn đoán và điều trị bệnh.', 'Đại học', 6),
(4, 'RH01', 'Răng hàm mặt', 'Ngành đào tạo bác sĩ răng hàm mặt, chẩn đoán và điều trị các bệnh răng miệng.', 'Đại học', 6),
(4, 'DS01', 'Dược học', 'Ngành đào tạo dược sĩ, nghiên cứu và phát triển thuốc.', 'Đại học', 5),

-- Đại học Sư phạm Hà Nội
(5, 'SP01', 'Sư phạm Toán học', 'Ngành đào tạo giáo viên dạy Toán học tại các trường phổ thông.', 'Đại học', 4),
(5, 'SP02', 'Sư phạm Vật lý', 'Ngành đào tạo giáo viên dạy Vật lý tại các trường phổ thông.', 'Đại học', 4),
(5, 'SP03', 'Sư phạm Hóa học', 'Ngành đào tạo giáo viên dạy Hóa học tại các trường phổ thông.', 'Đại học', 4),

-- Đại học Bách khoa TP.HCM
(6, 'IT02', 'Công nghệ thông tin', 'Ngành đào tạo chuyên sâu về lập trình, phát triển phần mềm và công nghệ thông tin.', 'Đại học', 4),
(6, 'EE02', 'Điện tử viễn thông', 'Ngành đào tạo về điện tử, viễn thông và các công nghệ truyền thông.', 'Đại học', 4),
(6, 'ME02', 'Cơ khí', 'Ngành đào tạo về thiết kế và chế tạo máy móc, hệ thống cơ khí.', 'Đại học', 4),

-- Đại học Kinh tế TP.HCM
(7, 'BA02', 'Quản trị kinh doanh', 'Ngành đào tạo về quản lý doanh nghiệp và phát triển kinh doanh.', 'Đại học', 4),
(7, 'FI02', 'Tài chính ngân hàng', 'Ngành đào tạo về tài chính, ngân hàng và đầu tư.', 'Đại học', 4),
(7, 'AC02', 'Kế toán', 'Ngành đào tạo về kế toán và kiểm toán.', 'Đại học', 4),

-- Đại học Y Dược TP.HCM
(8, 'YD02', 'Y đa khoa', 'Ngành đào tạo bác sĩ đa khoa.', 'Đại học', 6),
(8, 'RH02', 'Răng hàm mặt', 'Ngành đào tạo bác sĩ răng hàm mặt.', 'Đại học', 6),
(8, 'DS02', 'Dược học', 'Ngành đào tạo dược sĩ.', 'Đại học', 5),

-- Đại học Cần Thơ
(9, 'AG01', 'Nông nghiệp', 'Ngành đào tạo về nông nghiệp, trồng trọt và chăn nuôi.', 'Đại học', 4),
(9, 'FS01', 'Thủy sản', 'Ngành đào tạo về nuôi trồng và chế biến thủy sản.', 'Đại học', 4),
(9, 'IT03', 'Công nghệ thông tin', 'Ngành đào tạo về công nghệ thông tin và ứng dụng.', 'Đại học', 4),

-- Đại học Đà Nẵng
(10, 'IT04', 'Công nghệ thông tin', 'Ngành đào tạo về công nghệ thông tin và phát triển phần mềm.', 'Đại học', 4),
(10, 'BA03', 'Quản trị kinh doanh', 'Ngành đào tạo về quản lý doanh nghiệp.', 'Đại học', 4),
(10, 'CE02', 'Xây dựng', 'Ngành đào tạo về thiết kế và thi công công trình.', 'Đại học', 4);

-- Thêm dữ liệu điểm chuẩn
INSERT INTO admission_scores (major_id, year, block, min_score, quota, note) VALUES
-- Điểm chuẩn năm 2024
-- Bách khoa Hà Nội
(1, 2024, 'A00', 28.5, 150, 'Điểm chuẩn cao nhất'),
(1, 2024, 'A01', 28.0, 100, 'Khối A01'),
(2, 2024, 'A00', 27.5, 120, 'Điện tử viễn thông'),
(2, 2024, 'A01', 27.0, 80, 'Khối A01'),
(3, 2024, 'A00', 26.5, 100, 'Cơ khí'),
(3, 2024, 'A01', 26.0, 80, 'Khối A01'),
(4, 2024, 'A00', 26.0, 80, 'Xây dựng'),
(4, 2024, 'A01', 25.5, 60, 'Khối A01'),

-- Kinh tế Quốc dân
(5, 2024, 'A00', 27.0, 200, 'Quản trị kinh doanh'),
(5, 2024, 'A01', 26.5, 150, 'Khối A01'),
(5, 2024, 'D01', 26.0, 100, 'Khối D01'),
(6, 2024, 'A00', 26.5, 150, 'Tài chính ngân hàng'),
(6, 2024, 'A01', 26.0, 100, 'Khối A01'),
(6, 2024, 'D01', 25.5, 80, 'Khối D01'),
(7, 2024, 'A00', 26.0, 120, 'Kế toán'),
(7, 2024, 'A01', 25.5, 80, 'Khối A01'),
(8, 2024, 'A00', 25.5, 100, 'Marketing'),
(8, 2024, 'A01', 25.0, 80, 'Khối A01'),

-- Ngoại thương
(9, 2024, 'A00', 28.0, 150, 'Kinh tế đối ngoại'),
(9, 2024, 'A01', 27.5, 100, 'Khối A01'),
(9, 2024, 'D01', 27.0, 120, 'Khối D01'),
(10, 2024, 'A00', 27.5, 120, 'Thương mại quốc tế'),
(10, 2024, 'A01', 27.0, 80, 'Khối A01'),
(10, 2024, 'D01', 26.5, 100, 'Khối D01'),
(11, 2024, 'A00', 27.0, 100, 'Quản trị kinh doanh quốc tế'),
(11, 2024, 'A01', 26.5, 80, 'Khối A01'),
(11, 2024, 'D01', 26.0, 80, 'Khối D01'),

-- Y Hà Nội
(12, 2024, 'B00', 29.0, 200, 'Y đa khoa - điểm chuẩn cao nhất'),
(12, 2024, 'A00', 28.5, 50, 'Khối A00'),
(13, 2024, 'B00', 28.5, 80, 'Răng hàm mặt'),
(13, 2024, 'A00', 28.0, 20, 'Khối A00'),
(14, 2024, 'B00', 28.0, 100, 'Dược học'),
(14, 2024, 'A00', 27.5, 30, 'Khối A00'),

-- Sư phạm Hà Nội
(15, 2024, 'A00', 25.0, 100, 'Sư phạm Toán'),
(15, 2024, 'A01', 24.5, 80, 'Khối A01'),
(16, 2024, 'A00', 24.5, 80, 'Sư phạm Vật lý'),
(16, 2024, 'A01', 24.0, 60, 'Khối A01'),
(17, 2024, 'B00', 24.0, 80, 'Sư phạm Hóa học'),
(17, 2024, 'A00', 23.5, 60, 'Khối A00'),

-- Bách khoa TP.HCM
(18, 2024, 'A00', 28.0, 200, 'Công nghệ thông tin'),
(18, 2024, 'A01', 27.5, 150, 'Khối A01'),
(19, 2024, 'A00', 27.0, 150, 'Điện tử viễn thông'),
(19, 2024, 'A01', 26.5, 100, 'Khối A01'),
(20, 2024, 'A00', 26.0, 120, 'Cơ khí'),
(20, 2024, 'A01', 25.5, 80, 'Khối A01'),

-- Kinh tế TP.HCM
(21, 2024, 'A00', 26.5, 200, 'Quản trị kinh doanh'),
(21, 2024, 'A01', 26.0, 150, 'Khối A01'),
(21, 2024, 'D01', 25.5, 100, 'Khối D01'),
(22, 2024, 'A00', 26.0, 150, 'Tài chính ngân hàng'),
(22, 2024, 'A01', 25.5, 100, 'Khối A01'),
(23, 2024, 'A00', 25.5, 120, 'Kế toán'),
(23, 2024, 'A01', 25.0, 80, 'Khối A01'),

-- Y Dược TP.HCM
(24, 2024, 'B00', 28.5, 200, 'Y đa khoa'),
(24, 2024, 'A00', 28.0, 50, 'Khối A00'),
(25, 2024, 'B00', 28.0, 80, 'Răng hàm mặt'),
(25, 2024, 'A00', 27.5, 20, 'Khối A00'),
(26, 2024, 'B00', 27.5, 100, 'Dược học'),
(26, 2024, 'A00', 27.0, 30, 'Khối A00'),

-- Cần Thơ
(27, 2024, 'A00', 22.0, 100, 'Nông nghiệp'),
(27, 2024, 'B00', 21.5, 80, 'Khối B00'),
(28, 2024, 'A00', 21.5, 80, 'Thủy sản'),
(28, 2024, 'B00', 21.0, 60, 'Khối B00'),
(29, 2024, 'A00', 23.0, 120, 'Công nghệ thông tin'),
(29, 2024, 'A01', 22.5, 80, 'Khối A01'),

-- Đà Nẵng
(30, 2024, 'A00', 24.0, 150, 'Công nghệ thông tin'),
(30, 2024, 'A01', 23.5, 100, 'Khối A01'),
(31, 2024, 'A00', 23.5, 120, 'Quản trị kinh doanh'),
(31, 2024, 'A01', 23.0, 80, 'Khối A01'),
(32, 2024, 'A00', 23.0, 100, 'Xây dựng'),
(32, 2024, 'A01', 22.5, 80, 'Khối A01'),

-- Điểm chuẩn năm 2023 (để so sánh)
-- Một số ngành có điểm chuẩn năm 2023
(1, 2023, 'A00', 28.0, 150, 'Năm 2023'),
(1, 2023, 'A01', 27.5, 100, 'Năm 2023'),
(5, 2023, 'A00', 26.5, 200, 'Năm 2023'),
(5, 2023, 'A01', 26.0, 150, 'Năm 2023'),
(12, 2023, 'B00', 28.5, 200, 'Năm 2023'),
(12, 2023, 'A00', 28.0, 50, 'Năm 2023');

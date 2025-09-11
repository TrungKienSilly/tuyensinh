# 🎓 Hệ thống quản lý trường đại học tuyển sinh

Website quản lý thông tin các trường đại học đang tuyển sinh trên toàn quốc, bao gồm thông tin trường, ngành đào tạo và điểm chuẩn.

## ✨ Tính năng chính

### 🌐 Frontend (Người dùng)
- **Trang chủ**: Hiển thị danh sách trường đại học với tìm kiếm và lọc
- **Tìm kiếm nâng cao**: Tìm kiếm theo nhiều tiêu chí (tên trường, tỉnh, loại trường, ngành, điểm chuẩn)
- **Chi tiết trường**: Xem thông tin chi tiết trường và danh sách ngành đào tạo
- **Chi tiết điểm chuẩn**: Xem điểm chuẩn theo từng ngành, khối và năm

### 🔧 Admin Panel
- **Dashboard**: Thống kê tổng quan hệ thống
- **Quản lý trường**: CRUD thông tin trường đại học
- **Quản lý ngành**: CRUD ngành đào tạo theo trường
- **Quản lý điểm chuẩn**: CRUD điểm chuẩn theo ngành và năm
- **Import dữ liệu**: Upload file CSV để cập nhật hàng loạt

## 🛠️ Công nghệ sử dụng

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **UI Framework**: Custom CSS với responsive design

## 📋 Cài đặt

### 1. Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Web server (Apache/Nginx)
- Hoặc sử dụng XAMPP/WAMP/Laragon

### 2. Cài đặt database
```sql
-- Tạo database
CREATE DATABASE university_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import file database.sql
mysql -u root -p university_management < database.sql

-- Import dữ liệu mẫu
mysql -u root -p university_management < sample_data.sql
```

### 3. Cấu hình
Chỉnh sửa file `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'university_management');
```

### 4. Chạy ứng dụng
- Mở trình duyệt và truy cập: `http://localhost/truongdaihoc/`
- Admin panel: `http://localhost/truongdaihoc/admin/`
- Tài khoản admin mặc định: `admin` / `password`

## 📊 Cấu trúc database

### Bảng `universities`
- Thông tin trường đại học (tên, mã, địa chỉ, website, v.v.)

### Bảng `majors`
- Ngành đào tạo của từng trường

### Bảng `admission_scores`
- Điểm chuẩn theo ngành, năm và khối thi

### Bảng `admin_users`
- Tài khoản quản trị viên

## 🎯 Hướng dẫn sử dụng

### Cho người dùng
1. **Tìm kiếm trường**: Sử dụng ô tìm kiếm trên trang chủ
2. **Lọc theo tiêu chí**: Chọn tỉnh/thành phố, loại trường
3. **Xem chi tiết**: Click vào "Xem chi tiết" để xem thông tin đầy đủ
4. **Tìm kiếm nâng cao**: Sử dụng trang "Tìm kiếm nâng cao" để tìm theo điểm chuẩn

### Cho admin
1. **Đăng nhập**: Truy cập `/admin/` và đăng nhập
2. **Quản lý trường**: Thêm/sửa/xóa thông tin trường
3. **Quản lý ngành**: Thêm/sửa/xóa ngành đào tạo
4. **Quản lý điểm chuẩn**: Cập nhật điểm chuẩn theo năm

## 📁 Cấu trúc thư mục

```
truongdaihoc/
├── assets/
│   └── css/
│       └── style.css          # CSS chính
├── config/
│   └── database.php           # Cấu hình database
├── admin/                     # Admin panel
│   ├── index.php             # Dashboard
│   ├── login.php             # Đăng nhập
│   ├── logout.php            # Đăng xuất
│   ├── universities.php      # Quản lý trường
│   ├── majors.php            # Quản lý ngành
│   ├── scores.php            # Quản lý điểm chuẩn
│   └── import.php            # Import dữ liệu
├── index.php                 # Trang chủ
├── university.php            # Chi tiết trường
├── search.php                # Tìm kiếm nâng cao
├── database.sql              # Cấu trúc database
├── sample_data.sql           # Dữ liệu mẫu
└── README.md                 # Hướng dẫn này
```

## 🔧 Tính năng nâng cao

### Import CSV
- Hỗ trợ import dữ liệu điểm chuẩn từ file CSV
- Format: `major_code,year,block,min_score,quota,note`

### Responsive Design
- Giao diện thân thiện trên mọi thiết bị
- Mobile-first approach

### Tìm kiếm thông minh
- Tìm kiếm theo tên trường, ngành học
- Lọc theo điểm chuẩn, tỉnh thành
- Sắp xếp kết quả theo nhiều tiêu chí

## 🚀 Phát triển thêm

### Tính năng có thể thêm
- [ ] API REST cho mobile app
- [ ] Hệ thống đăng ký nguyện vọng
- [ ] Thống kê và báo cáo nâng cao
- [ ] Hệ thống thông báo
- [ ] Tích hợp Google Maps
- [ ] Hệ thống đánh giá trường

### Cải thiện hiệu suất
- [ ] Caching với Redis
- [ ] CDN cho static files
- [ ] Database indexing
- [ ] Lazy loading

## 📞 Hỗ trợ

Nếu gặp vấn đề trong quá trình cài đặt hoặc sử dụng, vui lòng:
1. Kiểm tra log lỗi của web server
2. Đảm bảo PHP và MySQL đã được cài đặt đúng
3. Kiểm tra quyền truy cập file và thư mục

## 📄 License

Dự án này được phát hành dưới giấy phép MIT. Xem file LICENSE để biết thêm chi tiết.

---

**Tác giả**: Hệ thống quản lý trường đại học  
**Phiên bản**: 1.0.0  
**Cập nhật cuối**: 2024


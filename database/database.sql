CREATE DATABASE IF NOT EXISTS cms_commune;
USE cms_commune;

-- جدول المستخدمين
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                    role ENUM('admin','employee') DEFAULT 'employee',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        );

                        -- جدول المواطنين
                        CREATE TABLE citizens (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                                cin VARCHAR(20) NOT NULL UNIQUE,
                                    first_name VARCHAR(100) NOT NULL,
                                        last_name VARCHAR(100) NOT NULL,
                                            birth_date DATE,
                                                gender ENUM('Male','Female'),
                                                    address TEXT,
                                                        phone VARCHAR(20),
                                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                                            );

                                                            -- جدول أنواع الوثائق
                                                            CREATE TABLE document_types (
                                                                id INT AUTO_INCREMENT PRIMARY KEY,
                                                                    name VARCHAR(100) NOT NULL
                                                                    );

                                                                    -- جدول طلبات الوثائق
                                                                    CREATE TABLE documents (
                                                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                                                            citizen_id INT NOT NULL,
                                                                                document_type_id INT NOT NULL,
                                                                                    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
                                                                                        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                                                            FOREIGN KEY (citizen_id) REFERENCES citizens(id) ON DELETE CASCADE,
                                                                                                FOREIGN KEY (document_type_id) REFERENCES document_types(id)
                                                                                                );

                                                                                                -- جدول سجل العمليات
                                                                                                CREATE TABLE activity_logs (
                                                                                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                                                                                        user_id INT NOT NULL,
                                                                                                            action TEXT NOT NULL,
                                                                                                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                                                                                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                                                                                                                    );

                                                                                                                    -- إدخال أنواع الوثائق
                                                                                                                    INSERT INTO document_types (name) VALUES
                                                                                                                    ('Birth Certificate'),
                                                                                                                    ('Residence Certificate'),
                                                                                                                    ('Marriage Certificate'),
                                                                                                                    ('Death Certificate');
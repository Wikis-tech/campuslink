INSERT INTO admin_users (
    full_name,
    email,
    password,
    role,
    is_active,
    created_at
) VALUES (
    'Super Admin',
    'campuslinkd@gmail.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uJCmCkZiy',
    'super_admin',
    1,
    NOW()
);


UPDATE admin_users 
SET password = '$2y$12$RAcpbXUAVG5n9J6rAiwYIecCP8mTLXup9FbyNQyrwXXrLgEG3REca' 
WHERE email = 'campuslinkd@gmail.com';
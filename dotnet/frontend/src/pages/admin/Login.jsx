import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Input from '../../components/Input';
import Button from '../../components/Button';

export default function Login() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');
    
    try {
      const response = await fetch('http://localhost:8085/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
      });
      
      if (response.ok) {
        const data = await response.json();
        localStorage.setItem('admin_token', data.token);
        localStorage.setItem('admin_role', data.role);
        navigate('/admin');
      } else {
        const err = await response.json();
        setError(err.message || 'เข้าสู่ระบบไม่สำเร็จ');
      }
    } catch (err) {
      setError('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="max-w-md w-full bg-white p-8 rounded-lg shadow-md border-t-4 border-primary">
        <div className="text-center mb-8">
          <h2 className="text-2xl font-bold text-primary mb-2">Admin Login</h2>
          <p className="text-textMuted text-sm">เข้าสู่ระบบจัดการข้อมูลการลงทะเบียน</p>
        </div>
        
        {error && (
          <div className="bg-red-50 border-l-4 border-error text-error p-3 mb-6 rounded text-sm">
            {error}
          </div>
        )}

        <form onSubmit={handleLogin}>
          <Input 
            label="ชื่อผู้ใช้งาน" 
            id="username" 
            value={username} 
            onChange={(e) => setUsername(e.target.value)} 
            required 
          />
          <Input 
            label="รหัสผ่าน" 
            id="password" 
            type="password" 
            value={password} 
            onChange={(e) => setPassword(e.target.value)} 
            required 
          />
          
          <Button type="submit" className="w-full justify-center mt-6">
            เข้าสู่ระบบ
          </Button>
        </form>
      </div>
    </div>
  );
}

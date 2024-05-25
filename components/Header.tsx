// components/Header.tsx

import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';

const Header = () => {
  const [user, setUser] = useState(null);
  const router = useRouter();

  useEffect(() => {
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
      setUser(JSON.parse(storedUser));
    }
  }, []);

  const handleLogout = () => {
    localStorage.removeItem('user');
    setUser(null);
    router.push('/login');
  };

  return (
    <div className="bg-white shadow p-4 flex justify-between items-center">
      <div className="text-xl font-bold">Users</div>
      {user ? (
        <button
          onClick={handleLogout}
          className="bg-red-600 text-white px-4 py-2 rounded"
        >
          Logout
        </button>
      ) : (
        <a href="/login" className="bg-blue-500 text-white px-4 py-2 rounded">
          Sign In
        </a>
      )}
    </div>
  );
};

export default Header;

// pages/dashboard.tsx

import { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import Layout from '../components/Layout';
import NotesList from '../components/NotesList';

export default function Dashboard() {
  const [user, setUser] = useState(null);
  const router = useRouter();

  useEffect(() => {
    const storedUser = localStorage.getItem('user');
    if (!storedUser) {
      router.push('/login');
    } else {
      setUser(JSON.parse(storedUser));
    }
  }, [router]);

  const handleLogout = () => {
    localStorage.removeItem('user');
    router.push('/login');
  };

  if (!user) return <p>Loading...</p>;

  return (
    <Layout>
      <div className="p-8 bg-white rounded shadow-md w-full">
        <h1 className="text-3xl font-bold mb-6 text-center text-blue-600">Dashboard</h1>
        <p className="mb-6 text-center">Welcome, {user.display_name}!</p>
        <NotesList />
    
      </div>
    </Layout>
  );
}

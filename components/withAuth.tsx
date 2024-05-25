// components/withAuth.tsx

import { useEffect } from 'react';
import { useRouter } from 'next/router';

const withAuth = (WrappedComponent) => {
  return (props) => {
    const router = useRouter();

    useEffect(() => {
      const user = localStorage.getItem('user');
      if (!user) {
        router.replace('/login');
      }
    }, [router]);

    const user = localStorage.getItem('user');
    if (!user) {
      return null; // Render nothing while redirecting
    }

    return <WrappedComponent {...props} />;
  };
};

export default withAuth;

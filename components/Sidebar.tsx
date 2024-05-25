// components/Sidebar.tsx
import Link from 'next/link';

const Sidebar = () => {
  return (
    <div className="h-screen bg-gray-800 text-white w-64 p-4">
      <div className="text-2xl font-bold mb-8">ACME</div>
      <nav>
        <ul>
          <li className="mb-4">
            <Link href="/users"
              className="flex items-center">
                <span className="material-icons">group</span>
                <span className="ml-2">Users</span>
       
            </Link>
          </li>
          <li className="mb-4">
            <Link href="/settings"
            className="flex items-center">
                <span className="material-icons">settings</span>
                <span className="ml-2">Settings</span>
        
            </Link>
          </li>
          <li className="mb-4">
            <Link href="/deploy"
             className="flex items-center">
                <span className="material-icons">cloud_upload</span>
                <span className="ml-2">Deploy</span>
          
            </Link>
          </li>
        </ul>
      </nav>
    </div>
  );
};

export default Sidebar;

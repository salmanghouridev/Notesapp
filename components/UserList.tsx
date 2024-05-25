// components/UserList.tsx
import { useState } from 'react';

const UserList = () => {
  const [users, setUsers] = useState([
    { name: 'Clementine Bauch', email: 'clementine@gmail.com', username: '@clementine' },
    { name: 'Jamie Johnson', email: 'jamiejohnson@example.com', username: 'jamiejohnson' },
    { name: 'Chris Lee', email: 'chrislee@example.com', username: 'chrislee' },
    // Add more users as needed
  ]);

  return (
    <div>
      <div className="mb-4">
        <input
          type="text"
          placeholder="Search users..."
          className="w-full px-4 py-2 border rounded"
        />
      </div>
      <table className="min-w-full bg-white">
        <thead>
          <tr>
            <th className="py-2">Name</th>
            <th className="py-2">Email</th>
            <th className="py-2">Username</th>
            <th className="py-2">Action</th>
          </tr>
        </thead>
        <tbody>
          {users.map((user, index) => (
            <tr key={index}>
              <td className="border px-4 py-2">{user.name}</td>
              <td className="border px-4 py-2">{user.email}</td>
              <td className="border px-4 py-2">{user.username}</td>
              <td className="border px-4 py-2">
                <button className="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default UserList;

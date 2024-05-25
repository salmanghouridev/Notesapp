// pages/api/auth.ts

import type { NextApiRequest, NextApiResponse } from 'next';
import axios from 'axios';

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'POST') {
    res.setHeader('Allow', ['POST']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
    return;
  }

  const { username, password } = req.body;

  if (!username || !password) {
    res.status(400).json({ error: 'Username and password are required' });
    return;
  }

  try {
    const response = await axios.post(`${process.env.WORDPRESS_SITE_URL}/wp-json/custom/v1/authenticate`, {
      username,
      password,
    });

    if (response.status === 200) {
      res.status(200).json({ user: response.data });
    } else {
      res.status(response.status).json({ error: 'Invalid credentials' });
    }
  } catch (error) {
    res.status(error.response.status).json({ error: error.response.data.message });
  }
}

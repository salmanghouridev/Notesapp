// pages/api/notes.ts
import { getSession } from 'next-auth/react';
import { NextApiRequest, NextApiResponse } from 'next';
import axios from 'axios';

const handler = async (req: NextApiRequest, res: NextApiResponse) => {
  const session = await getSession({ req });

  if (!session) {
    return res.status(401).json({ message: 'Unauthorized' });
  }

  try {
    const response = await axios.get('https://your-woocommerce-site.com/wp-json/wp-sticky-notes/v1/notes');
    const notes = response.data;
    return res.status(200).json(notes);
  } catch (error) {
    return res.status(500).json({ message: 'Failed to fetch notes' });
  }
};

export default handler;

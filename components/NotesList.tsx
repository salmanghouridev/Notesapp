import React, { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import { RootState, useAppDispatch } from '../redux/store';
import { fetchNotes } from '../redux/notesSlice';

const NotesList: React.FC = () => {
  const dispatch = useAppDispatch();
  const { notes, loading, error } = useSelector((state: RootState) => state.notes || { notes: [], loading: false, error: null });
  const [currentPage, setCurrentPage] = useState(1);
  const [notesPerPage] = useState(4);

  useEffect(() => {
    dispatch(fetchNotes());
  }, [dispatch]);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  // Get current notes
  const indexOfLastNote = currentPage * notesPerPage;
  const indexOfFirstNote = indexOfLastNote - notesPerPage;
  const currentNotes = notes.slice(indexOfFirstNote, indexOfLastNote);

  // Change page
  const paginate = (pageNumber: number) => setCurrentPage(pageNumber);

  return (
    <div className="mx-auto container py-10 px-6">
      <div className="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {currentNotes.map((note) => (
          <div key={note.id} className="rounded-lg shadow-lg bg-white border border-gray-200 p-4">
            <div className="flex flex-col h-full">
              <div className="flex-1 mb-4">
                <h4 className="text-gray-800 font-bold mb-2">{note.heading}</h4>
                <div className="text-gray-600 text-sm whitespace-pre-wrap overflow-y-auto max-h-32">
                  {note.description}
                </div>
              </div>
              <div className="flex items-center justify-between text-gray-600 mt-auto">
                <p className="text-sm">{new Date(note.created_at).toLocaleDateString()}</p>
                <button
                  className="w-8 h-8 rounded-full bg-gray-800 text-white flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-800"
                  aria-label="edit note"
                  role="button"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="icon icon-tabler icon-tabler-pencil"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    strokeWidth="1.5"
                    stroke="currentColor"
                    fill="none"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                  >
                    <path stroke="none" d="M0 0h24v24H0z" />
                    <path d="M4 20h4l10.5 -10.5a1.5 1.5 0 0 0 -4 -4l-10.5 10.5v4" />
                    <line x1="13.5" y1="6.5" x2="17.5" y2="10.5" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
      <div className="flex justify-center mt-8">
        <ul className="flex">
          {[...Array(Math.ceil(notes.length / notesPerPage)).keys()].map(number => (
            <li key={number + 1} className="mx-1">
              <button
                onClick={() => paginate(number + 1)}
                className={`px-3 py-1 rounded ${currentPage === number + 1 ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-700'}`}
              >
                {number + 1}
              </button>
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
};

export default NotesList;

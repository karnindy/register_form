using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;

namespace backend.Repositories
{
    public class RegisterHistoryRepository : IRegisterHistoryRepository
    {
        private readonly AppDbContext _context;

        public RegisterHistoryRepository(AppDbContext context)
        {
            _context = context;
        }

        public async Task<IEnumerable<RegisterHistory>> GetAllAsync()
        {
            return await _context.RegisterHistories
                .OrderByDescending(h => h.CreatedAt)
                .ToListAsync();
        }

        public async Task<IEnumerable<RegisterHistory>> GetByRegisterIdAsync(int registerId)
        {
            return await _context.RegisterHistories
                .Where(h => h.RegisterId == registerId)
                .OrderByDescending(h => h.CreatedAt)
                .ToListAsync();
        }
    }
}

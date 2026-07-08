using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;

namespace backend.Repositories
{
    public class RegisterRepository : IRegisterRepository
    {
        private readonly AppDbContext _context;

        public RegisterRepository(AppDbContext context)
        {
            _context = context;
        }

        public async Task<Register?> GetByIdAsync(int id)
        {
            return await _context.Registers.FindAsync(id);
        }

        public async Task<IEnumerable<Register>> GetAllAsync()
        {
            return await _context.Registers.OrderByDescending(r => r.Id).ToListAsync();
        }

        public async Task<int> CreateAsync(Register register)
        {
            register.CreatedAt = DateTime.Now;
            _context.Registers.Add(register);
            await _context.SaveChangesAsync();

            var history = new RegisterHistory
            {
                RegisterId = register.Id,
                EditedByType = "applicant",
                CreatedBy = "user",
                OldData = null,
                NewData = System.Text.Json.JsonSerializer.Serialize(register),
                CreatedAt = DateTime.Now
            };
            _context.RegisterHistories.Add(history);
            await _context.SaveChangesAsync();

            return register.Id;
        }

        public async Task UpdateAsync(Register register)
        {
            var oldRegister = await _context.Registers.AsNoTracking().FirstOrDefaultAsync(r => r.Id == register.Id);
            string? oldData = null;
            if (oldRegister != null)
            {
                oldData = System.Text.Json.JsonSerializer.Serialize(oldRegister);
            }

            register.UpdatedAt = DateTime.Now;
            _context.Registers.Update(register);
            
            var history = new RegisterHistory
            {
                RegisterId = register.Id,
                EditedByType = "applicant",
                CreatedBy = "user",
                OldData = oldData,
                NewData = System.Text.Json.JsonSerializer.Serialize(register),
                CreatedAt = DateTime.Now
            };
            _context.RegisterHistories.Add(history);

            await _context.SaveChangesAsync();
        }

        public async Task DeleteAsync(int id)
        {
            var register = await _context.Registers.FindAsync(id);
            if (register != null)
            {
                _context.Registers.Remove(register);
                await _context.SaveChangesAsync();
            }
        }
    }
}
